<?php

// -------------------------------------------------------------------
// config\framework\pathfinder
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cache\LocalStorage;
use Core\Pathfinder;
use const Support\PLACEHOLDER_ARRAY;

return static function( ContainerConfigurator $container ) : void {
    // Cache
    $container->services()
        ->set( 'cache.pathfinder', LocalStorage::class )
        ->call(
            'setStopwatch',
            [service( 'debug.stopwatch' )->ignoreOnInvalid()],
        )
        ->tag( 'monolog.logger', ['channel' => 'cache'] )
        ->args( ['%kernel.cache_dir%/pathfinder_cache.php'] );

    // Pathfinder - Find and return registered paths
    $container->services()
        ->set( Pathfinder::class )
        ->tag( 'monolog.logger', ['channel' => 'pathfinder'] )
        ->tag( 'controller.service_arguments' )
        ->args(
            [
                PLACEHOLDER_ARRAY, // $parameters
                service( 'parameter_bag' ),
                service( 'logger' )->nullOnInvalid(),
                service( 'cache.pathfinder' ),
                service( 'debug.stopwatch' )->nullOnInvalid(),
                true, // $deferCacheCommits
            ],
        );
};
