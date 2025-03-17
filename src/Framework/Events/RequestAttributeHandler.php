<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Response\{DocumentResponse, ViewResponse};
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestAttributeHandler
{
    public function __invoke( RequestEvent $event ) : void
    {
        $htmx = $event->getRequest()->headers->has( 'hx-request' );
        $path = $event->getRequest()->getRequestUri();

        // Set Request attributes
        $event->getRequest()->attributes->set( '_response', $htmx ? ViewResponse::class : DocumentResponse::class );
        $event->getRequest()->attributes->set( '_path', $path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
    }
}
