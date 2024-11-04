<?php

namespace Core\Framework\Lifecycle;

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, ViewEvent};
use function Support\get_class_name;

final class ControllerContent
{
    use ServiceContainer;

    private ?string $controller = null;

    private bool $isHtmxRequest = false;

    public function __construct() {}

    public function onKernelController( ControllerEvent $event ) : void
    {
        $this->controller = get_class_name( $event->getController(), true );

        if ( ! \is_subclass_of( $this->controller, Controller::class, true ) ) {
            $this->controller = null;
            return;
        }

        $this->isHtmxRequest = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( '_htmx_request', $this->isHtmxRequest );

        dump( $this );
    }

    #[NoReturn]
    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->controller ) {
            dd( $event );
        }
    }

    #[NoReturn]
    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->controller ) {
            dd( $event );
        }
    }
}
