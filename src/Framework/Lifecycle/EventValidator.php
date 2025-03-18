<?php

declare(strict_types=1);

namespace Core\Framework\Lifecycle;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

trait EventValidator
{
    private static bool $skipEvent;

    final protected function validateLifecycle( RequestEvent $event ) : void
    {
        static::$skipEvent = false;

        if ( $event->getRequestType() === HttpKernelInterface::MAIN_REQUEST ) {
            static::$skipEvent = true;
        }
    }

    final protected function skip() : bool
    {
        return static::$skipEvent ?? false;
    }
}
