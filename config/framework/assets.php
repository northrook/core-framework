<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, AssetManager\AssetManifest, Pathfinder};
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()
        ->set( 'path.asset_manifest', '%dir.var%/asset.manifest' )
        ->set( 'dir.assets', '%dir.root%/assets' )
        ->set( 'dir.assets.meta', '%dir.var%/assets' )
        ->set( 'dir.assets.cache', '%dir.cache%/assets' );

    $container->services()
        ->set( AssetManifest::class )
        ->args( [param( 'path.asset_manifest' ), param( 'dir.assets.meta' )] );

    // Create a ServiceLocator for ServicePasses
    $container->services()
        ->set( AssetManager::LOCATOR_ID )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );

    $container->services()
        ->set( AssetManager::class )
        ->args(
            [
                param( 'dir.assets.meta' ),
                service( Pathfinder::class ),
                service( AssetManager::LOCATOR_ID ),
                service( 'cache.asset_pool' )->nullOnInvalid(),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'assets'] )
        ->public();

    //
    // $service = $container->services()
    //     ->defaults()
    //     ->tag( 'monolog.logger', ['channel' => 'assets'] )
    //     ->autoconfigure();
    //
    // //
    // $service->set( 'core.asset_config', AssetConfig::class )
    //     ->args(
    //         [
    //             service( Pathfinder::class ),
    //             ['dir.assets', 'dir.core.assets'], // $assetDirectories
    //             ['dir.config/assets.php', 'dir.core.config/assets.php'], // $configFiles
    //         ],
    //     );
    //
    // $service->set( AssetManifest::class )
    //     ->args(
    //         [
    //             service( 'core.asset_config' ),
    //             service( 'cache.asset_pool' ),
    //             service( 'logger' )->nullOnInvalid(),
    //         ],
    //     );
    //
    // $service->set( AssetManager::class )
    //     ->args(
    //         [
    //             service( 'core.asset_config' ),
    //             service( Pathfinder::class ),
    //             service( RegisterAssetServices::ID ),
    //             service( AssetManifest::class ),
    //             service( 'cache.asset_pool' ),
    //         ],
    //     )
    //     ->autowire()
    //     ->public();
};
