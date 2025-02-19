<?php

declare(strict_types=1);

namespace Core\Framework\Profiler;

use Northrook\Clerk;
use Symfony\Component\Cache\DataCollector\CacheDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{TerminateEvent};

final readonly class CacheProfiler implements EventSubscriberInterface
{
    public function __construct(
            CacheDataCollector $cacheDataCollector
    ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.terminate' => 'onKernelTerminate',
        ];
    }

    public function onKernelTerminate( TerminateEvent $event ) : void
    {
        dump( $this );
    }
}
