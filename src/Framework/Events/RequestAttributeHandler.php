<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Lifecycle\EventValidator;
use Core\Framework\Response\{View};
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 *
 * @see    RequestEvent
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class RequestAttributeHandler
{
    use EventValidator;

    public function __invoke( RequestEvent $event ) : void
    {
        dump( __METHOD__.' '.( $this->skip() ? 'true' : 'false') );
        $this->validateLifecycle( $event );
        dump( __METHOD__.' '.( $this->skip() ? 'true' : 'false') );

        $htmx      = $event->getRequest()->headers->has( 'hx-request' );
        $_path     = $event->getRequest()->getRequestUri();
        $_response = $htmx ? View::CONTENT : View::DOCUMENT;

        // Set Request attributes
        $event->getRequest()->attributes->set( '_response', $_response );
        $event->getRequest()->attributes->set( '_path', $_path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
    }
}
