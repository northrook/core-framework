<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;

return static function( ContainerConfigurator $container ) : void {
    // $assets = $container->services()
    //     ->defaults()
    //     ->tag( 'core.asset' );

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
