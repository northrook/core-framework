<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, Pathfinder};
use Core\AssetManager\RegisteredAsset;
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
    $service = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'assets'] )
        ->autoconfigure();

    $service->set( AssetManager::class )
        ->args(
            [
                param( 'dir.assets.meta' ),
                service( Pathfinder::class ),
                service( RegisteredAsset::LOCATOR_ID ),
                service( 'cache.asset_pool' )->nullOnInvalid(),
            ],
        )
        ->autowire()
        ->public();

    // // Create a ServiceLocator for ServicePasses
    // $container->services()->set( RegisterAssetServices::ID )
    //     ->tag( 'container.service_locator' )
    //     ->args( PLACEHOLDER_ARGS );
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
