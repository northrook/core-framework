<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Core\Framework\Response\{View, ViewResponse};
use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Stringable;

final class ResponseHandler extends ControllerAwareEvent implements EventSubscriberInterface
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

        $this->profiler?->event( __METHOD__ );

        $content = $event->getControllerResult();

        if ( $content instanceof Stringable ) {
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

        $_response = $event->getRequest()->attributes->get( '_response' );

        if ( $_response instanceof View ) {
            $event->setResponse(
                new ViewResponse( $_response, $content ),
            );
        }
        else {
            $event->setResponse( new Response( $content ) );
        }

        $this->profiler?->stop( __METHOD__ );
        dump( $event );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        $this->profiler?->event( __METHOD__ );
        if ( $this->skipEvent() ) {
            return;
        }

        if ( $event->getResponse() instanceof ViewResponse ) {
            return;
        }

        $_response_view = $event->getRequest()->attributes->get( '_response' );

        if ( $_response_view instanceof View ) {
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
            $this->logger?->error( "Expected a 'View::TYPE' on this 'ResponseEvent'." );
        }

        $this->profiler?->stop( __METHOD__ );
        dump( $event );
    }
}
