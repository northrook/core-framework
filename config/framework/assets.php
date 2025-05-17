<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, AssetManager\AssetManifest, Pathfinder};
use function Support\normalize_path;
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
    foreach ( [
        'path.asset_manifest' => '%dir.var%/asset.manifest',
        'dir.assets'          => '%dir.root%/assets',
        'dir.assets.meta'     => '%dir.var%/assets',
        'dir.assets.cache'    => '%dir.cache%/assets',
    ] as $key => $value ) {
        $container->parameters()->set( $key, normalize_path( $value ) );
    }

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
                [
                    param( 'dir.assets' ),
                    param( 'dir.core.assets' ),
                ],
                service( AssetManifest::class ),
                service( Pathfinder::class ),
                service( AssetManager::LOCATOR_ID ),
                service( 'cache.asset_pool' )->nullOnInvalid(),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'assets'] )
        ->public();
};
