<?php

namespace Core\Framework\Lifecycle;

// early: do On{Type} attributed callbacks
//        generate from View and Response

// later: ensure headers etc

use Core\Framework\Controller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, ViewEvent};
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
final class ResponseHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => ['parseController', 192],
            KernelEvents::VIEW       => ['handleResponse'],
            KernelEvents::RESPONSE   => ['handleResponse'],
        ];
    }

    /**
     * @param ControllerEvent $event
     *
     * @return void
     */
    public function parseController( ControllerEvent $event ) : void
    {
        if ( \is_array( $event->getController() ) && $event->getController()[0] instanceof Controller ) {

            dd(
                $event,
                $event->getRequest(),
                $event->getController(),
                $event->getControllerReflector(),
                $this,
            );
            // $this->controller    = $event->getController()[0]::class;
            // $this->isHtmxRequest = $event->getRequest()->headers->has( 'hx-request' );
            //
            // $event->getRequest()->attributes->set( '_document_template', $this->getControllerTemplate() );
            // $event->getRequest()->attributes->set(
            //     '_content_template',
            //     $this->getMethodTemplate( $event->getControllerReflector() ),
            // );
        }
    }

    public function handleResponse( ResponseEvent|ViewEvent $event ) : void
    {
        dump( __METHOD__, $event );
    }
}
