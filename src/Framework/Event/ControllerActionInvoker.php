<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Controller;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent, ControllerEvent};
use Core\Framework\Response\Template;
use InvalidArgumentException;
use function Support\str_before;
use ReflectionAttribute;
use ReflectionClass;

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
     * @param ControllerEvent $event
     */
    public function __invoke( ControllerEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        // Get Template::attr from both Controller:class and Controller::method
        // Merge the two, return single Template
        [$controller, $method] = $this->resolveController( $event );

        if ( $controller instanceof Controller ) {
            $controller->setCurrentRequest( $event->getRequest() );
        }
        else {
            $this->logger?->notice( 'Non-framework Controller, skipping.' );
            self::$handleLifecycleEvent = false;
            return;
        }

        $_template = $this->resolveViewTemplate( $event, $controller );

        $event->getRequest()->attributes->set(
            '_template',
            $_template,
        );

        dd( \get_defined_vars() );
        // try {
        //     $reflection = new ReflectionClass( $controller );
        //
        //     $reflection->getMethod( 'controllerResponseMethods' )
        //         ->invoke( $controller );
        // }
        // catch ( ReflectionException $exception ) {
        //     $this->logger?->error(
        //         $exception->getMessage(),
        //         ['exception' => $exception],
        //     );
        // }
    }

    private function resolveViewTemplate( ControllerEvent $event, object|string $controller ) : Template
    {
        $template = ( $event->getControllerReflector()->getAttributes(
            Template::class,
            ReflectionAttribute::IS_INSTANCEOF,
        )[0] ?? null )?->newInstance();

        if ( $template instanceof Template && $template->content && $template->document ) {
            return $template;
        }

        $controllerTemplate = ( ( new ReflectionClass( $controller ) )->getAttributes(
            Template::class,
            ReflectionAttribute::IS_INSTANCEOF,
        )[0] ?? null )?->newInstance() ?? new Template();

        return  $template;
    }

    /**
     * @param ControllerEvent $event
     *
     * @return array{0: class-string|object, 1: string}
     */
    private function resolveController( ControllerEvent $event ) : array
    {
        $controller = $event->getController();

        if ( \is_object( $controller ) ) {
            return [$controller::class, '__invoke'];
        }

        if ( \is_string( $controller ) ) {
            [$controller, $method] = \explode( '::', $controller, 2 );
        }
        elseif ( \is_array( $controller ) ) {
            [$controller, $method] = $controller;
        }
        else {
            throw new InvalidArgumentException( 'Unable to resolve the controller class.' );
        }

        \assert(
            ( \is_string( $controller ) && \class_exists( $controller, false ) )
                || \is_object( $controller ),
        );

        \assert( \is_string( $method ) );

        return [$controller, $method];
    }

    // /**
    //  * @param ControllerEvent $event
    //  *
    //  * @return class-string|object
    //  */
    // private function resolveController( ControllerEvent $event ) : object|string
    // {
    //     $controller = $event->getController();
    //
    //     $controller = match ( true ) {
    //         \is_object( $controller ) => $controller,
    //         \is_array( $controller )  => $controller[0] ?? '',
    //         \is_string( $controller ) => str_before( $controller, '::' ),
    //         default                   => '',
    //     };
    //
    //     \assert( \is_string( $controller ) || \is_object( $controller ) );
    //
    //     if ( \is_object( $controller ) || \class_exists( $controller ) ) {
    //         return $controller;
    //     }
    //
    //     throw new InvalidArgumentException( 'Unable to resolve the controller class.' );
    // }
}
