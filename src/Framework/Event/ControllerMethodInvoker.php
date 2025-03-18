<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Controller;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent};
use ReflectionException;
use ReflectionClass;
use InvalidArgumentException;

/**
 * {@see ControllerArgumentsEvent}
 *
 * Calls {@see Controller} methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @final  ✅
 */
final class ControllerMethodInvoker extends LifecycleEvent
{
    /**
     * @param ControllerArgumentsEvent $event
     */
    public function __invoke( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->controllerOnViewMethods( $event );
    }

    private function controllerOnViewMethods( ControllerArgumentsEvent $event ) : void
    {
        $controller = $this->resolveController( $event );

        try {
            ( new ReflectionClass( $controller ) )
                ->getMethod( 'controllerResponseMethods' )
                ->invoke( $controller );
        }
        catch ( ReflectionException $exception ) {
            $this->logger?->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
    }

    /**
     * @param ControllerArgumentsEvent $event
     *
     * @return object
     */
    private function resolveController( ControllerArgumentsEvent $event ) : object
    {
        $controller = $event->getController();

        $controller = match ( true ) {
            \is_object( $controller ) => $controller,
            \is_array( $controller )  => $controller[0] ?? false,
            default                   => false,
        };

        if ( \is_object( $controller ) ) {
            return $controller;
        }

        throw new InvalidArgumentException( 'Unable to resolve the controller class.' );
    }
}
