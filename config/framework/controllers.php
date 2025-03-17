<?php

// -------------------------------------------------------------------
// config\framework\controllers
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\{Events\ControllerEventHandler, ResponseRenderer};
use Core\Controller\{AssetController, PublicController, SecurityController};

return static function( ContainerConfigurator $container ) : void {

    $framework = $container->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $framework->set( AssetController::class );

    $framework->set( SecurityController::class );

    $framework->set( PublicController::class );
    // ->autowire();

    // $framework
    //     ->set( AdminController::class )
    //     ->autowire();
};
