<?php

declare(strict_types=1);

namespace Core\Framework\Profiler;

use Northrook\Clerk;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, ResponseEvent, TerminateEvent};

final readonly class ClerkProfiler implements EventSubscriberInterface
{
    public function __construct( private Clerk $monitor ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.request'              => 'onKernelRequest',
            'kernel.controller'           => 'onKernelController',
            'kernel.controller_arguments' => 'onKernelControllerArguments',
            'kernel.view'                 => 'onKernelView',
            'kernel.response'             => 'onKernelResponse',
            'kernel.finish_request'       => 'onKernelFinishRequest',
            'kernel.exception'            => 'onKernelException',
            'kernel.terminate'            => 'onKernelTerminate',
        ];
    }

    public function onKernelRequest() : void
    {
        $this->monitor->event( 'onKernelRequest' );
    }

    public function onKernelController() : void
    {
        $this->monitor->event( 'onKernelController' );
        // $this->monitor->stopwatch->stop( 'onKernelRequest' );
    }

    public function onKernelControllerArguments() : void
    {
        $this->monitor->stop( 'onKernelController' );
    }

    public function onKernelView() : void
    {
        $this->monitor->event( 'onKernelView' );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        $this->monitor->event( 'onKernelResponse' );
    }

    public function onKernelFinishRequest() : void
    {
        $this->monitor->event( 'onKernelFinishRequest' );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        $this->monitor->event( 'onKernelException' );
    }

    public function onKernelTerminate( TerminateEvent $event ) : void
    {
        $this->monitor->event( 'onKernelTerminate' );

        foreach ( $this->monitor->getEvents() as $event ) {
            $event->stop();
        }

        $this->monitor->reset( true );
    }
}
