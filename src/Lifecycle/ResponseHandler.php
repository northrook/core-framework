<?php

namespace Core\Framework\Lifecycle;

// early: do On{Type} attributed callbacks
//        generate from View and Response

// later: ensure headers etc

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, TerminateEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;
use Reflector;

/**
 * Handles {@see Response} events for controllers extending the {@see Controller}.
 *
 * - Output parsing
 * - Response header parsing
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class ResponseHandler implements EventSubscriberInterface
{
    use ServiceContainer;

    private bool $shouldHandle;

    private readonly Reflector $controllerReflection;

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => ['parseController', 192],
            KernelEvents::VIEW       => ['handleResponse'],
            KernelEvents::RESPONSE   => ['handleResponse'],
            KernelEvents::TERMINATE  => ['onKernelTerminate'],
        ];
    }

    /**
     * @param ControllerEvent $event
     *
     * @return void
     */
    public function parseController( ControllerEvent $event ) : void
    {
        $this->shouldHandle = ( \is_array( $event->getController() ) && $event->getController()[0] instanceof Controller );

        if ( ! $this->shouldHandle ) {
            return;
        }

        $event->getRequest()->attributes->set( '_htmx_request', $event->getRequest()->headers->has( 'hx-request' ) );

        $this->controllerReflection = $event->getControllerReflector();

        dump( $event );
    }

    public function handleResponse( ResponseEvent|ViewEvent $event ) : void
    {
        if ( ! $this->shouldHandle ) {
            return;
        }


    }

    public function onKernelTerminate( TerminateEvent $event ) : void
    {
        if ( $this->shouldHandle ) {
            dump( __METHOD__, $event );
        }
    }
}
