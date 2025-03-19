<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Lifecycle\{LifecycleEvent};
use Core\Framework\Response\View;
use Symfony\Component\HttpKernel\Event\{RequestEvent};

/**
 * @internal
 *
 * @see    RequestEvent
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class RequestLifecycleHandler extends LifecycleEvent
{
    public function __invoke( RequestEvent $event ) : void
    {
        self::$handleLifecycleEvent = $this->handleRequest( $event );

        $profiler = $this->profiler?->event( 'prepare.request' );

        $htmx  = $event->getRequest()->headers->has( 'hx-request' );
        $_path = $event->getRequest()->getRequestUri();
        $_view = $htmx ? View::CONTENT : View::DOCUMENT;

        // Set Request attributes
        $event->getRequest()->attributes->set( '_view', $_view );
        $event->getRequest()->attributes->set( '_path', $_path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );

        $profiler?->stop();
    }

    private function handleRequest( RequestEvent $event ) : bool
    {
        // Only parse GET requests
        if ( $event->getRequest()->isMethod( 'GET' ) === false ) {
            return false;
        }

        // Do not parse sub-requests
        if ( $event->isMainRequest() === false ) {
            $this->logger?->notice(
                'Lifecycle: Sub-request, skipping.',
                ['request' => $event->getRequest()],
            );
            return false;
        }

        return true;
    }
}
