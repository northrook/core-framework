<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Controller\ControllerEventSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\{KernelEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::VIEW     => 'onKernelView',
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
            // KernelEvents::EXCEPTION => 'onKernelException',
            // KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
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
