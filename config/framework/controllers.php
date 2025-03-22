<?php

// -------------------------------------------------------------------
// config\framework\controllers
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Controller\{AdminController, AssetController, PublicController, SecurityController};
use Core\Framework\Routing\AdminRouteLoader;
use Core\Symfony\DependencyInjection\SettingsProvider;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( AdminRouteLoader::class )
        ->args(
            [
                param( 'kernel.environment' ),
                service( SettingsProvider::class ),
            ],
        );

    $framework = $container->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $framework->set( AdminController::class );

    $framework->set( AssetController::class );

    $framework->set( SecurityController::class );

    $framework->set( PublicController::class );
    // ->autowire();

    // $framework
    //     ->set( AdminController::class )
    //     ->autowire();
};
