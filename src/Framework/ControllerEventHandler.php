<?php

namespace Core\Framework;

use Core\Framework\Controller\ControllerEventSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\{KernelEvent, RequestEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST  => 'onKernelRequest',
            KernelEvents::VIEW     => 'onKernelView',
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
            // KernelEvents::EXCEPTION => 'onKernelException',
            // KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    /**
     * Parse the incoming {@see RequestEvent}:
     * - Determine type: `xhr` for client fetch request, otherwise `http`.
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest( RequestEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }
        dump( $event, $this->eventPath( $event ) );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }
        dump( $event, $this->eventPath( $event ) );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }
        dump( $event, $this->eventPath( $event ) );
    }

    protected function eventPath( KernelEvent $event ) : string
    {
        dump( \spl_object_id( $this ), \spl_object_id( $event ) );
        return $event->getRequest()->getRequestUri();
    }
}
