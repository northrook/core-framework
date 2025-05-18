<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\AssetManager;
use Core\Framework\Response\Parameters;
use Core\Interface\SettingsProviderInterface;
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
use Core\View\{DocumentEngine, Template\Engine};

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->alias( SettingsProviderInterface::class, 'core.settings_provider' );

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
        ->args(
            [
                service( DocumentEngine::class ),
                service( Engine::class ),
                service( Parameters::class ),
                service( AssetManager::class ),
                service( ToastService::class ),
            ],
        );

    $subscriber->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] );
};
