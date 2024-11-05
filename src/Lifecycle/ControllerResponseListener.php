<?php

namespace Core\Framework\Lifecycle;

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpFoundation\Request;
use function Support\get_class_name;

final class ControllerResponseListener
{
    use ServiceContainer;

    public function __construct() {}

    public function onKernelController( ControllerEvent $event ) : void
    {
        if ( ! $this->handle( $event->getRequest() ) ) {
            return;
        }

        $isHtmx = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( '_htmx_request', $isHtmx );
        $event->getRequest()->attributes->set( '_request_type', $isHtmx ? 'document' : 'content' );

        dump( $this );
    }

    public function handleResponse( ResponseEvent|ViewEvent $event ) : void
    {
        if ( $this->handle( $event->getRequest() ) ) {
            dd( $event );
        }
    }

    /**
     * Check if the passed {@see Request} is extending the {@see \Core\Framework\Controller}.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function handle( Request $request ) : bool
    {
        $controller = $request->attributes->get( '_controller' );

        \assert( \is_string( $controller ) );

        return \is_subclass_of( get_class_name( $controller ), Controller::class );
    }
}
