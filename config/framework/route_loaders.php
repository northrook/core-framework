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

return static function( ContainerConfigurator $container ) : void {
    $router = $container->services()
        ->defaults()
        ->autowire()
        ->tag( 'routing.loader' );

    $router->set( AdminRouteLoader::class );

    $router->set( AssetsRouteLoader::class );

    $router->set( PublicRouteLoader::class );

    $router->set( SecurityRouteLoader::class );

    $router->set( SystemRouteLoader::class );
};
