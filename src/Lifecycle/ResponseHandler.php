<?php

namespace Core\Framework\Lifecycle;

// early: do On{Type} attributed callbacks
//        generate from View and Response

// later: ensure headers etc

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles {@see Response} events for controllers extending the {@see \Core\Framework\Controller}.
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
    #[Override]
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::VIEW     => ['handleView'],
            KernelEvents::RESPONSE => ['handleResponse'],
        ];
    }

    public function handleView( ViewEvent $event ) : void
    {
        dump( $event );
    }

    public function handleResponse( ResponseEvent $event ) : void
    {
        dump( $event );
    }
}
