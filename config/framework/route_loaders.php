<?php

// -------------------------------------------------------------------
// config\framework\route_loaders
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Routing\{
    AdminRouteLoader,
    AssetsRouteLoader,
    PublicRouteLoader,
    SecurityRouteLoader,
    SystemRouteLoader,
};
use Core\Symfony\DependencyInjection\SettingsProvider;

return static function( ContainerConfigurator $container ) : void {
    $router_args = [
        param( 'kernel.environment' ),
        service( SettingsProvider::class ),
    ];

    $router = $container->services()
        ->defaults()
        ->tag( 'routing.loader' );

    $router->set( AdminRouteLoader::class )
        ->args( $router_args );

    $router->set( AssetsRouteLoader::class )
        ->args( $router_args );

    $router->set( PublicRouteLoader::class )
        ->args( $router_args );

    $router->set( SecurityRouteLoader::class )
        ->args( $router_args );

    $router->set( SystemRouteLoader::class )
        ->args( $router_args );
};
