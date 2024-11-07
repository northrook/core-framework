<?php

// -------------------------------------------------------------------
// config\framework\lifecycle
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Controller\{ResponseListener};
use Core\Framework\Telemetry\{LifecycleProfiler, PipelineCollector};
use Northrook\Clerk;
use Symfony\Component\Stopwatch\Stopwatch;

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // Response EventSubscriber;
        ->set( ResponseListener::class )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.controller'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.view'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.response'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.terminate'] )

            // Stopwatch
        ->set( Clerk::class )
        ->args(
            [
                service( Stopwatch::class ),
                true, // single instance
                true, // throw on reinstantiation attempt
                param( 'kernel.debug' ), // only enable when debugging
            ],
        )

            // TelemetryEventSubscriber
        ->set( LifecycleProfiler::class )
        ->tag( 'kernel.event_subscriber' )
        ->args(
            [
                service( Clerk::class ),
                service( 'profiler' ),
            ],
        )

            // Profiler
        ->set( PipelineCollector::class )
        ->tag( 'data_collector' );
};
