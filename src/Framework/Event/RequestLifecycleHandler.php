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

        $htmx      = $event->getRequest()->headers->has( 'hx-request' );
        $_path     = $event->getRequest()->getRequestUri();
        $_response = $htmx ? View::CONTENT : View::DOCUMENT;

        // Set Request attributes
        $event->getRequest()->attributes->set( '_response', $_response );
        $event->getRequest()->attributes->set( '_path', $_path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
    }

    private function handleRequest( RequestEvent $event ) : bool
    {
        // Only parse GET requests
        if ( $event->getRequest()->isMethod( 'GET' ) !== false ) {
            return false;
        }

        // Do not parse sub-requests
        if ( $event->isMainRequest() === false ) {
            dump( ['sub-request' => $event] );
            return false;
        }

        return true;
    }
}
