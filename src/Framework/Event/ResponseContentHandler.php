<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Lifecycle\LifecycleEvent;
use Core\Framework\Response\{ResponseType, ViewResponse};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use InvalidArgumentException;
use function Support\is_stringable;

final class ResponseContentHandler extends LifecycleEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::VIEW     => ['onKernelView', 32],
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
        ];
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

        $this->profilerStart( 'controller.view' );

        $content = $this->resolveViewContent( $event->getControllerResult() );

        $_response = $event->getRequest()->attributes->get( '_view' );

        if ( $_response instanceof ResponseType ) {
            $event->setResponse( new ViewResponse( $_response, $content ) );
        }
        else {
            $event->setResponse( new Response( $content ) );
        }

        $this->profilerStop( 'controller.view' );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() || $event->getResponse() instanceof ViewResponse ) {
            return;
        }

        $this->profilerStart( 'controller.response' );

        $_response_view = $event->getRequest()->attributes->get( '_view' );

        if ( $_response_view instanceof ResponseType ) {
            $event->setResponse(
                new ViewResponse(
                    $_response_view,
                    $event->getResponse()->getContent(),
                    $event->getResponse()->getStatusCode(),
                    $event->getResponse()->headers->allPreserveCase(),
                ),
            );
        }
        else {
            $this->log(
                message : 'Expected a {type} on this {event}.',
                context : ['type' => ResponseType::class, 'event' => $event],
                level   : 'error',
            );
        }

        $this->profilerStop( 'controller.response' );
    }

    private function resolveViewContent( mixed $value ) : string
    {
        return match ( true ) {
            is_stringable( $value ) => (string) $value,
            default                 => throw new InvalidArgumentException(
                'Unable to resolve controller content.',
            ),
        };
    }
}
