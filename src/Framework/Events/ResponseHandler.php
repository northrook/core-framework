<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerEventSubscriber;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use ReflectionClass;
use ReflectionException;
use Stringable;

final class ResponseHandler extends ControllerEventSubscriber
{
    public function __construct() {}

    public static function getSubscribedEvents() : array
    {
        return [
            // KernelEvents::CONTROLLER_ARGUMENTS => 'handleControllerMethods',
            KernelEvents::VIEW     => ['onKernelView', 32],
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
        ];
    }

    /**
     * Call {@see Controller} methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
     *
     * @param ControllerArgumentsEvent $event
     */
    public function handleControllerMethods( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->skipEvent()
             || $event->getRequest()->attributes->has( '_controller_methods' )
        ) {
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

    /**
     * Generate and set an appropriate {@see Response}.
     *
     * @param ViewEvent $event
     *
     * @return void
     */
    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->profiler?->event( __METHOD__ );

        $content = $event->getControllerResult();

        if ( \is_string( $content ) || $content instanceof Stringable ) {
            $content = (string) $content;
        }

        if ( ! ( \is_string( $content ) || \is_null( $content ) ) ) {
            $this->logger?->error(
                message : 'Controller {controller} return value is {type}; {required}, {provided} provided as fallback.',
                context : [
                    'controller' => $this->controller,
                    'type'       => \gettype( $content ),
                    'required'   => 'string|null',
                    'provided'   => 'null',
                ],
            );
            $content = null;
        }

        // @phpstan-ignore-next-line
        \assert( \is_string( $content ) || \is_null( $content ) );

        $event->setResponse( new Response( $content ) );
        $this->profiler?->stop( __METHOD__ );
        dump( $event );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        dump( $event );
    }
}
