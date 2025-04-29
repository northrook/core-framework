<?php

// -------------------------------------------------------------------
// config\framework\cache
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cache\LocalStorage;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use const Support\CACHE_FOREVER;
use const Time\HOUR_4;

return static function( ContainerConfigurator $container ) : void {
    $cachePool = $container->services()
        ->defaults()
        ->tag( 'cache.pool' );

    $cachePool
        ->set( 'cache.asset_pool', PhpFilesAdapter::class )
        ->args( ['asset_pool', CACHE_FOREVER, '%kernel.cache_dir%', true] );

    $cachePool
        ->set( 'cache.component_pool', PhpFilesAdapter::class )
        ->args( ['component_pool', HOUR_4, '%kernel.cache_dir%', true] );

    $container->services()
        ->set( 'cache.pathfinder', LocalStorage::class )
        ->call(
            'setStopwatch',
            [service( 'debug.stopwatch' )->ignoreOnInvalid()],
        )
        ->tag( 'monolog.logger', ['channel' => 'cache'] )
        ->args( ['%kernel.cache_dir%/pathfinder_cache.php'] );
};
