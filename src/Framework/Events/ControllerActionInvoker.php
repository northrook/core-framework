<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

final class ControllerActionInvoker extends ControllerAwareEvent
{
    public function __invoke( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        try {
            ( new ReflectionClass( $this->controller ) )
                ->getMethod( 'controllerResponseMethods' )
                ->invoke( $this->controller );
        }
        catch ( ReflectionException $exception ) {
            $this->logger?->error( $exception->getMessage(), ['exception' => $exception] );
        }
    }
}
