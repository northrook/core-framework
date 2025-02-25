<?php

namespace Core\Framework\Profiler;

use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class ProfilerBar
{
    public function __invoke( TerminateEvent $event ) : void
    {
        // Idea is to inject a simple Profiler, and link to the Symfony Profiler
        // using the x-debug-token from Response Headers.
        dump( $event );
    }
}
