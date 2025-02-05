<?php

namespace Core\Framework;

use Core\Symfony\DependencyInjection\Autodiscover;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Event\{KernelEvent, RequestEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;

#[Autodiscover(
    tag      : ['monolog.logger' => ['channel' => 'controller']],
    autowire : true,
)]
final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct(
        #[Autowire( service : 'cache.core.http_event' )]
        protected readonly CacheInterface  $cache,
        // #[Autowire( service : 'logger' )] // autodiscover
        protected readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST  => 'onKernelRequest',
            KernelEvents::VIEW     => 'onKernelView',
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
            // KernelEvents::EXCEPTION => 'onKernelException',
            // KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    /**
     * Parse the incoming {@see RequestEvent}:
     * - Determine type: `xhr` for client fetch request, otherwise `http`.
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest( RequestEvent $event ) : void
    {
        dump( $event, $this->eventPath( $event ) );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        dump( $event, $this->eventPath( $event ) );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        dump( $event, $this->eventPath( $event ) );
    }

    private function cacheEvent() : string
    {
        return $this->cache->get( 'ControllerEvent', fn() => __CLASS__ );
    }

    protected function eventPath( KernelEvent $event ) : string
    {
        dump( \spl_object_id( $this ), \spl_object_id( $event ) );
        return $event->getRequest()->getRequestUri();
    }
}
