<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\Event\{TerminateEvent};

final readonly class DeferredCachePoolHandler
{
    public function __construct(
        private CacheItemPoolInterface $assetCache,
    ) {}

    public function __invoke( TerminateEvent $event ) : void
    {
        dump( $this->assetCache );
        $this->assetCache->commit();
    }
}
