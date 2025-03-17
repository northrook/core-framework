<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\ResponseRenderer;
use Core\Symfony\ToastService;
use Core\Framework\Events\{ControllerActionInvoker,
    ControllerEventHandler,
    RequestAttributeHandler,
    ResponseHandler,
    ToastMessageInjector
};

return static function( ContainerConfigurator $container ) : void {
    $subscriber = $container->services()
        ->defaults()
        ->tag( 'kernel.event_subscriber' );

    $listener = $container->services()
        ->defaults()
        ->tag( 'kernel.event_listener' );

    $listener->set( ControllerActionInvoker::class );

    $listener->set( RequestAttributeHandler::class );

    $subscriber->set( ControllerEventHandler::class )
        ->args( [service( ResponseRenderer::class )] )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $subscriber->set( ResponseHandler::class );

    $subscriber->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] );
};
