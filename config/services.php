<?php

declare(strict_types=1);

use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaHandler;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\FileInspectorInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\FileStorageInterface;
use AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\DoctrineMediaRepository;
use AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\ImmediateEventPublisher;
use AlexandreBulete\DddMediaBundle\Infrastructure\Storage\FlysystemFileStorage;
use AlexandreBulete\DddMediaBundle\Infrastructure\Storage\NativeFileInspector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddMediaBundle\\', $src . '/')
        ->exclude([
            // Aggregates and value objects are never services.
            $src . '/Domain',
            $src . '/DddMediaBundle.php',
            // Doctrine instantiates types itself.
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Mapping',
            // Conditional: services_migrations.php, services_admin.php.
            $src . '/Infrastructure/Doctrine/Migrations',
            $src . '/Infrastructure/Sylius',
        ]);

    $services->set(RemoveMediaHandler::class)
        ->arg('$usages', tagged_iterator('media.usage'));

    // ── Ports → adapters ────────────────────────────────────────────────────
    // Rebind any of these to change behaviour without touching the bundle.
    $services->alias(MediaRepositoryInterface::class, DoctrineMediaRepository::class);
    $services->alias(DomainEventPublisherInterface::class, ImmediateEventPublisher::class);
    $services->alias(FileStorageInterface::class, FlysystemFileStorage::class);
    $services->alias(FileInspectorInterface::class, NativeFileInspector::class);
};
