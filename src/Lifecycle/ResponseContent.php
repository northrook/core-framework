<?php

namespace Core\Framework\Lifecycle;

use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};

final class ResponseContent
{
    public function onKernelView( ViewEvent $event ) : void
    {

        dump( $event );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {

        dump( $event );
    }
}
