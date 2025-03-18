<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Event\{
    RequestLifecycleHandler,
    ControllerActionInvoker,
    ResponseContentHandler,
    ResponseViewHandler,
    ToastMessageInjector
};
use Core\Framework\ResponseRenderer;
use Core\Symfony\ToastService;
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
    $listener->set( RequestLifecycleHandler::class );

    /**
     * {@see ControllerArgumentsEvent}.
     */
    $listener->set( ControllerActionInvoker::class );

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
