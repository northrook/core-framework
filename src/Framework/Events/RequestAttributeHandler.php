<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestAttributeHandler
{
    public function __invoke( RequestEvent $event ) : void
    {
        $htmx = $event->getRequest()->headers->has( 'hx-request' );

        // Set Request attributes
        $event->getRequest()->attributes->set( '_path', $event->getRequest()->getRequestUri() );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
        $event->getRequest()->attributes->set( 'view-type', $htmx ? 'content' : 'document' );
    }
}
