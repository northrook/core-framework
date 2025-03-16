<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, AssetManager\AssetManifest, Pathfinder};
use Core\AssetManager\AssetConfig;
use Core\AssetManager\Compiler\RegisterAssetServices;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
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

    // Create a ServiceLocator for ServicePasses
    $container->services()->set( RegisterAssetServices::ID )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );

    $service = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'assets'] )
        ->autoconfigure();

    //
    $service->set( 'core.asset_config', AssetConfig::class )
        ->args(
            [
                service( Pathfinder::class ),
                ['dir.assets', 'dir.core.assets'], // $assetDirectories
                ['dir.config/assets.php', 'dir.core.config/assets.php'], // $configFiles
            ],
        );

    $service->set( AssetManifest::class )
        ->args(
            [
                service( 'core.asset_config' ),
                service( 'cache.asset_pool' ),
                service( 'logger' )->nullOnInvalid(),
            ],
        );

    $service->set( AssetManager::class )
        ->args(
            [
                service( 'core.asset_config' ),
                service( Pathfinder::class ),
                service( RegisterAssetServices::ID ),
                service( AssetManifest::class ),
                service( 'cache.asset_pool' ),
            ],
        )
        ->autowire()
        ->public();

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
