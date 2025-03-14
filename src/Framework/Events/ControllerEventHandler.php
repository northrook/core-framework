<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerEventSubscriber;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent, ResponseEvent, ViewEvent};
use Core\Framework\ResponseRenderer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Stringable;
use ReflectionClass;
use ReflectionException;

final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct( protected readonly ResponseRenderer $responseRenderer ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'handleControllerMethods',
            KernelEvents::VIEW                 => 'onKernelView',
            KernelEvents::RESPONSE             => ['onKernelResponse', 32],
        ];
    }

    /**
     * Call {@see Controller} methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
     *
     * @param ControllerArgumentsEvent $event
     */
    public function handleControllerMethods( ControllerArgumentsEvent $event ) : void
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
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->profiler?->event( __METHOD__, 'View' );

        if ( $this->getSetting( 'view.template.clear_cache', false ) ) {
            $this->responseRenderer
                ->templateEngine
                ->clearTemplateCache();
        }

        $profileContent = $this->profiler?->event( 'Response Content', 'View' );
        $this->responseRenderer
            ->setResponseContent(
                $event,
                $this->template,
            );
        $profileContent?->stop();

        $profileRender = $this->profiler?->event( 'Render Response', 'View' );
        $event->setResponse(
            $this->responseRenderer->getResponse(),
        );
        $profileRender?->stop();

        $this->profiler?->stop( category : 'View' );
    }
}
