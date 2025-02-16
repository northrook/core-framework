<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, Pathfinder};
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( 'cache.asset_manager', PhpFilesAdapter::class )
        ->tag( 'cache.pool' )
        ->args(
            [
                'asset_manager',  // $namespace
                0,             // $defaultLifetime
                '%kernel.cache_dir%', // $directory
                true,          // $appendOnly
            ],
        );

    $container->services()
        ->set( 'cache.asset_pool', PhpFilesAdapter::class )
        ->tag( 'cache.pool' )
        ->args(
            [
                'asset_pool',  // $namespace
                0,             // $defaultLifetime
                '%kernel.cache_dir%', // $directory
                true,          // $appendOnly
            ],
        );

    $configDirectories = [
        'dir.config/assets.php',
        'dir.core.config/assets.php',
    ];
    $assetDirectories = [];

    // $assets = $container->services()
    //     ->defaults()
    //     ->tag( 'core.asset' );

    $service = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'assets'] )
        ->autoconfigure();

    $service->set( AssetManager::class )
        ->args(
            [
                $configDirectories,
                $assetDirectories,
                '%kernel.cache_dir%/assets',
                service( Pathfinder::class ),
                service( 'asset.service_locator' ),
                service( 'cache.asset_manager' ),
                service( 'cache.asset_pool' ),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
        ->autowire()
        ->public();

    $service->set( 'asset.service_locator' )
        ->tag( 'container.service_locator' );

    /**
     * Register AssetManifest as a service
     */
    // $container->services()
    //     ->set( AssetManifest::class )
    //     ->args( [param( 'path.asset_manifest' )] )
    //     ->tag( 'monolog.logger', ['channel' => 'assets'] )
    //     ->alias( AssetManifestInterface::class, AssetManifest::class );
    //
    // $container->services()
    //     ->set( AssetFactory::class )
    //         // ->lazy( true )
    //     ->args(
    //         [
    //             service( AssetManifest::class ),
    //             service( Pathfinder::class ),
    //             param( 'dir.assets' ),
    //             [
    //                 param( 'dir.assets' ),
    //                 param( 'dir.core.assets' ),
    //             ],
    //             service( 'logger' ),
    //         ],
    //     );
    //     // ->call( ...CoreStyle::callback( 'style.core' ) );
    //
    // $container->services()
    //         //
    //         // Framework Asset Manager
    //     ->set( AssetManager::class )
    //     ->args(
    //         [
    //             service( AssetFactory::class ),
    //             null, // cache
    //             service( 'logger' ),
    //         ],
    //     )
    //     ->tag( 'core.service_arguments' );
};
