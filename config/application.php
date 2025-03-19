<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\ResponseRenderer;
use Core\Symfony\ToastService;
use Core\Framework\Event\{
    ControllerMethodInvoker,
    RequestLifecycleHandler,
    ControllerActionInvoker,
    ResponseContentHandler,
    ResponseViewHandler,
    ToastMessageInjector
};
use Symfony\Component\HttpKernel\Event\{
    ControllerArgumentsEvent,
    ControllerEvent,
    RequestEvent,
    ResponseEvent,
    ViewEvent
};

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
    $listener->set( RequestLifecycleHandler::class );

    /**
     * {@see ControllerEvent}.
     */
    $listener->set( ControllerActionInvoker::class );

    /**
     * {@see ControllerArgumentsEvent}.
     */
    $listener->set( ControllerMethodInvoker::class );

    /**
     * Prepares content for {@see ViewEvent} and {@see ResponseEvent}.
     */
    // $subscriber->set( ResponseHandler::class );
    $subscriber->set( ResponseContentHandler::class );

    /**
     * @see ResponseEvent
     */
    $listener->set( ResponseViewHandler::class )
        ->args( [service( ResponseRenderer::class )] );

    $subscriber->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] );
};
