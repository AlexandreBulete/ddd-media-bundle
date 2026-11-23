<?php

declare(strict_types=1);

use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Grid\MediaGrid;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * Loaded only when `media.admin.enabled` is true: everything Sylius, nothing
 * else. Resources are DTOs, not services.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddMediaBundle\\Infrastructure\\Sylius\\', $src . '/Infrastructure/Sylius/')
        ->exclude([$src . '/Infrastructure/Sylius/Resource/MediaResource.php']);

    $services->set(MediaGrid::class)->args([param('media.admin.grid_limits')]);
};
