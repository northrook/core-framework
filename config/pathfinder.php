<?php

// -------------------------------------------------------------------
// config\framework\pathfinder
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cache\{LocalStorage, LocalStoragePool};
use Core\{Pathfinder, Interface\PathfinderInterface};

return static function( ContainerConfigurator $container ) : void {
    // Cache
    $container->services()
        ->set( 'core.pathfinder_cache', LocalStorage::class )
        ->tag( 'monolog.logger', ['channel' => 'pathfinder'] )
        ->args(
            [
                '%kernel.cache_dir%/pathfinder.cache', // $filePath
                'pathfinder.cache', // $name
                Pathfinder::class, // $generator
            ],
        );

    $container->services()
        ->set( 'cache.pathfinder', LocalStoragePool::class )
        ->tag( 'cache.pool' )
        ->args( ['%kernel.cache_dir%/pathfinder_cache.php'] );

    // Pathfinder
    // Find and return registered paths
    $container->services()
        ->set( Pathfinder::class )
        ->tag( 'monolog.logger', ['channel' => 'pathfinder'] )
        ->tag( 'controller.service_arguments' )
        ->args(
            [
                [], // $parameters
                service( 'parameter_bag' ),  // $parameterBag
                service( 'cache.pathfinder' ),                    // $cache
                service( 'logger' ),                // $logger
            ],
        )
        ->alias( PathfinderInterface::class, Pathfinder::class );
};
