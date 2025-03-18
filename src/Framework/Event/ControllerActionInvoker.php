<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Controller;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent, ControllerEvent};
use Core\Framework\Response\Template;
use InvalidArgumentException;
use ReflectionException;
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

        $parameters = [
            '_controller_class'  => $controller::class,
            '_controller_method' => $method,
            '_template'          => $this->resolveViewTemplate( $event, $controller ),
        ];

        $event->getRequest()->attributes->add( $parameters );
    }

    /**
     * @param ControllerEvent     $event
     * @param class-string|object $controller
     *
     * @return Template
     */
    private function resolveViewTemplate( ControllerEvent $event, object|string $controller ) : Template
    {
        $template = ( $event->getControllerReflector()->getAttributes(
            Template::class,
            ReflectionAttribute::IS_INSTANCEOF,
        )[0] ?? null )?->newInstance();

        if ( $template instanceof Template && $template->content && $template->document ) {
            return $template;
        }

        try {
            $controllerTemplate = ( ( new ReflectionClass( $controller ) )->getAttributes(
                Template::class,
                ReflectionAttribute::IS_INSTANCEOF,
            )[0] ?? null )?->newInstance() ?? new Template();
        }
        catch ( ReflectionException $e ) {
            $this->logger?->error( $e->getMessage() );
            $controllerTemplate = new Template();
        }

        if ( ! $template ) {
            return $controllerTemplate;
        }

        $template->document ??= $controllerTemplate->document;
        $template->content  ??= $controllerTemplate->content;

        return $template;
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

}
