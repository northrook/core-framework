<?php

// -------------------------------------------------------------------
// config\framework\controllers
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Controller\{AdminController, AssetController, PublicController, SecurityController};
use Core\Framework\Routing\{AdminRouteLoader, AssetsRouteLoader, PublicRouteLoader, SecurityRouteLoader};
use Core\Symfony\DependencyInjection\SettingsProvider;

return static function( ContainerConfigurator $container ) : void {
    $router_args = [
        param( 'kernel.environment' ),
        service( SettingsProvider::class ),
    ];

    $router = $container->services()
        ->defaults()
        ->tag( 'routing.loader' );

    $controller = $container->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $controller->set( AdminController::class );
    $router->set( AdminRouteLoader::class )
        ->args( $router_args );

    $controller->set( AssetController::class );
    $router->set( AssetsRouteLoader::class )
        ->args( $router_args );

    $controller->set( PublicController::class );
    $router->set( PublicRouteLoader::class )
        ->args( $router_args );

    $controller->set( SecurityController::class );
    $router->set( SecurityRouteLoader::class )
        ->args( $router_args );
};
