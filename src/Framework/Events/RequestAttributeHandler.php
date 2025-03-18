<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use JetBrains\PhpStorm\Deprecated;
use Core\Framework\Response\{View};
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 *
 * @see    RequestEvent
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
#[Deprecated]
final class RequestAttributeHandler
{
    public function __invoke( RequestEvent $event ) : void
    {

        $htmx      = $event->getRequest()->headers->has( 'hx-request' );
        $_path     = $event->getRequest()->getRequestUri();
        $_response = $htmx ? View::CONTENT : View::DOCUMENT;

        // Set Request attributes
        $event->getRequest()->attributes->set( '_response', $_response );
        $event->getRequest()->attributes->set( '_path', $_path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
    }
}
