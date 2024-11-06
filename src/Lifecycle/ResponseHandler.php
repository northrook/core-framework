<?php

namespace Core\Framework\Lifecycle;

// early: do On{Type} attributed callbacks
//        generate from View and Response

// later: ensure headers etc

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, TerminateEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;

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
#[Deprecated]
final class ResponseHandler implements EventSubscriberInterface
{
    use ServiceContainer;

    private bool $shouldHandle;

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
