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
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent, RequestEvent, ResponseEvent, ViewEvent};

return static function( ContainerConfigurator $container ) : void {
    $subscriber = $container->services()
        ->defaults()
        ->tag( 'kernel.event_subscriber' );

    $listener = $container->services()
        ->defaults()
        ->tag( 'kernel.event_listener' );

    /**
     * {@see RequestEvent}.
     */
    $listener->set( RequestAttributeHandler::class );

    /**
     * {@see ControllerArgumentsEvent}.
     */
    $listener->set( ControllerActionInvoker::class );

    /**
     * Prepares content for {@see ViewEvent} and {@see ResponseEvent}.
     */
    $subscriber->set( ResponseHandler::class );

    /**
     * @see ResponseEvent
     */
    $listener->set( ControllerEventHandler::class )
        ->args( [service( ResponseRenderer::class )] );

    $subscriber->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] );
};
