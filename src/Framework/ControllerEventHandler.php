<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Controller\ControllerEventSubscriber;
use Core\View\DocumentView;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct(
        protected readonly DocumentView    $documentView,
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
        dump( $event, $this );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }
        dump( $event, $this );
    }
}
