<?php

// -------------------------------------------------------------------
// config\framework\cache
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use const Support\CACHE_FOREVER;
use const Time\HOUR_4;

return static function( ContainerConfigurator $container ) : void {
    $directory = param( 'kernel.cache_dir' );
    $cachePool = $container->services()
        ->defaults()
        ->tag( 'cache.pool' );

    $cachePool
        ->set( 'cache.asset_pool', PhpFilesAdapter::class )
        ->args( ['asset_pool', CACHE_FOREVER, $directory, true] );

    $cachePool
        ->set( 'cache.component_pool', PhpFilesAdapter::class )
        ->args( ['component_pool', HOUR_4, $directory, true] );
};
