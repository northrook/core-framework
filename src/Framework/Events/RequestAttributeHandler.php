<?php

namespace Core\Framework\Events;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestAttributeHandler
{
    public function __invoke( RequestEvent $event ) : void
    {
        dump( $event );
    }
}
