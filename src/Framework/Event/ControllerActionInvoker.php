<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\Template;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent};
use InvalidArgumentException;
use ReflectionClass, ReflectionException;
use function Support\str_before;
use ReflectionAttribute;

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
final class ControllerActionInvoker extends LifecycleEvent
{
    /**
     * @param ControllerArgumentsEvent $event
     */
    public function __invoke( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $controller = $this->resolveController( $event );

        if ( $controller instanceof Controller ) {
            $controller->setCurrentRequest( $event->getRequest() );
        }
        else {
            self::$handleLifecycleEvent = false;
            return;
        }

        try {
            $reflection = new ReflectionClass( $controller );

            $event->getRequest()->attributes->set(
                '_template',
                $reflection->getAttributes(
                    Template::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                )[0]?->newInstance(),
            );

            $reflection->getMethod( 'controllerResponseMethods' )
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
     * @return class-string|object
     */
    private function resolveController( ControllerArgumentsEvent $event ) : object|string
    {
        $controller = $event->getController();

        $controller = match ( true ) {
            \is_object( $controller ) => $controller,
            \is_array( $controller )  => $controller[0] ?? '',
            \is_string( $controller ) => str_before( $controller, '::' ),
            default                   => '',
        };

        \assert( \is_string( $controller ) || \is_object( $controller ) );

        if ( \is_object( $controller ) || \class_exists( $controller ) ) {
            return $controller;
        }

        throw new InvalidArgumentException( 'Unable to resolve the controller class.' );
    }
}
