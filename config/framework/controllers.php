<?php

// -------------------------------------------------------------------
// config\framework\controllers
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Controller\{
    AdminController,
    AssetController,
    PublicController,
    SecurityController,
    SystemController,
};

return static function( ContainerConfigurator $container ) : void {
    $controller = $container->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $controller->set( AdminController::class );

    $controller->set( AssetController::class );

    $controller->set( PublicController::class );

    $controller->set( SecurityController::class );

    $controller->set( SystemController::class );
};
