<?php

declare(strict_types=1);

use AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\Migrations\Version20261123160000;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when DoctrineMigrationsBundle is enabled. Each migration is a
 * service named after its class, which is also its recorded version.
 *
 * Listed one by one, never scanned: a stray class must not turn into a
 * schema change.
 */
return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->tag('doctrine_migrations.migration');

    foreach ([Version20261123160000::class] as $migration) {
        $services->set($migration);
    }
};
