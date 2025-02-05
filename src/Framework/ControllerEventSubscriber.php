<?php

namespace Core\Framework;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(
    event  : 'kernel.request',
    method : 'validateRequestController',
)]
abstract class ControllerEventSubscriber
{
    protected bool $skipEvent;

    final public function validateRequestController( RequestEvent $event ) : void
    {
        $controller = $event->getRequest()->attributes->get( '_controller' );

        if ( \is_array( $controller ) ) {
            [$controllerObject, $method] = $controller;
        }
        elseif ( \is_object( $controller ) ) {
            $controllerObject = $controller;
        }
        else {
            return;
        }

        // Store the check for later events
        $this->skipEvent = $controllerObject instanceof Controller;
    }
}
