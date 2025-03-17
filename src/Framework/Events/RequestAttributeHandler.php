<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Core\Framework\Response\{DocumentResponse, ViewResponse};
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestAttributeHandler extends ControllerAwareEvent
{
    public function __invoke( RequestEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $htmx      = $event->getRequest()->headers->has( 'hx-request' );
        $_path     = $event->getRequest()->getRequestUri();
        $_response = $htmx ? ViewResponse::class : DocumentResponse::class;

        // Set Request attributes
        $event->getRequest()->attributes->set( '_response', $_response );
        $event->getRequest()->attributes->set( '_path', $_path );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
    }
}
