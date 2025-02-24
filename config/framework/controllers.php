<?php

// -------------------------------------------------------------------
// config\framework\controllers
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\{Events\ControllerEventHandler, ResponseRenderer};
use Core\Controller\{FaviconController, PublicController, SecurityController};

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( ControllerEventHandler::class )
        ->args(
            [
                service( ResponseRenderer::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $framework = $container->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $framework->set( SecurityController::class );

    $framework->set( FaviconController::class );

    $framework->set( PublicController::class );
    // ->autowire();

    // $framework
    //     ->set( AdminController::class )
    //     ->autowire();
};
