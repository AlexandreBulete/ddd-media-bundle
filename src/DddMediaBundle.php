<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle;

use AlexandreBulete\DddMediaBundle\Domain\Service\MediaUsageInterface;
use AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\Type\MediaIdType;
use AlexandreBulete\DddMediaBundle\Infrastructure\Storage\FlysystemFileStorage;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResourceFactory;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * A media library as a drop-in Bounded Context: files kept on a Flysystem
 * storage, described, resized on demand by LiipImagine, browsed and deleted
 * from a back office.
 *
 * Other contexts hold a media's id and nothing else. They render it with the
 * `media()` and `media_url()` Twig functions, and implement
 * MediaUsageInterface so that a file they show cannot be deleted.
 *
 * @phpstan-type MediaConfig array{max_size: string, storage: string|null, admin: array{enabled: bool, grid_limits: list<int>}}
 */
final class DddMediaBundle extends AbstractBundle
{
    public const string DEFAULT_STORAGE = 'media.storage';
    public const string LOADER = 'media';

    protected string $extensionAlias = 'media';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('max_size')
                    ->defaultValue('10M')
                    ->info('Largest accepted upload, in the File constraint format (10M, 500k).')
                ->end()
                ->scalarNode('storage')
                    ->defaultNull()
                    ->info('Id of a Flysystem storage service. Null: "media.storage", a local disk under var/storage/media.')
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back-office screens. Turn off for a headless deployment.')
                        ->end()
                        ->arrayNode('grid_limits')
                            ->integerPrototype()->end()
                            ->defaultValue([24, 48, 96])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var MediaConfig $config */
        $container->parameters()
            ->set('media.max_size', $config['max_size'])
            ->set('media.storage', $config['storage'] ?? self::DEFAULT_STORAGE)
            ->set('media.admin.grid_limits', $config['admin']['grid_limits']);

        $builder->registerForAutoconfiguration(MediaUsageInterface::class)->addTag('media.usage');

        $container->import($this->getPath() . '/config/services.php');
        // A service id, not a parameter: resolved here, once.
        $container->services()->get(FlysystemFileStorage::class)
            ->args([service($config['storage'] ?? self::DEFAULT_STORAGE)]);
        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }
        if (self::migrationsEnabled($builder)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Before the config tree is processed: defaults are not applied yet.
        $adminEnabled = true;
        $storage = null;
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig($this->extensionAlias);
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $adminEnabled = $admin['enabled'];
            }
            if (is_string($config['storage'] ?? null)) {
                $storage = $config['storage'];
            }
        }

        $builder->prependExtensionConfig('doctrine', [
            'dbal' => ['types' => [MediaIdType::NAME => MediaIdType::class]],
            'orm' => [
                'mappings' => [
                    'Media' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\DddMediaBundle\Domain\Model',
                        'alias' => 'Media',
                    ],
                ],
            ],
        ]);

        if (self::migrationsEnabled($builder)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }

        $builder->prependExtensionConfig('framework', [
            'translator' => ['paths' => [$this->getPath() . '/translations']],
        ]);

        // The default storage: a local disk inside the durable var/storage.
        // Another storage (S3…) is declared by the application and named in
        // `media.storage`, never by overriding this one.
        if ($storage === null) {
            $builder->prependExtensionConfig('flysystem', [
                'storages' => [
                    self::DEFAULT_STORAGE => [
                        'adapter' => 'local',
                        'options' => ['directory' => '%kernel.project_dir%/var/storage/media'],
                    ],
                ],
            ]);
        }

        // Resized copies are read from the library's storage. The loader is
        // the default one, so that the application's filter sets need not
        // name it; the bundle's own filter names it anyway.
        $builder->prependExtensionConfig('liip_imagine', [
            'twig' => ['mode' => 'lazy'],
            'loaders' => [
                self::LOADER => ['flysystem' => ['filesystem_service' => $storage ?? self::DEFAULT_STORAGE]],
            ],
            'data_loader' => self::LOADER,
            'filter_sets' => [
                MediaResourceFactory::THUMBNAIL_FILTER => [
                    'data_loader' => self::LOADER,
                    'quality' => 80,
                    'filters' => ['thumbnail' => ['size' => [112, 112], 'mode' => 'outbound']],
                ],
            ],
        ]);

        if ($adminEnabled) {
            // DddSyliusBundle only globs the application's own resources.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
        }
    }

    /**
     * From `kernel.bundles`: inside loadExtension() the builder is scoped to
     * this extension, so hasExtension() would answer false.
     */
    private static function migrationsEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DoctrineMigrationsBundle::class, $bundles, true);
    }
}
