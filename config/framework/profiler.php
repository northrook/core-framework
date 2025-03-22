<?php

// -------------------------------------------------------------------
// config\framework\profiler
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Profiler\{DevHotlinks, ParameterSettingsCollector, PipelineCollector, ProfilerBar};
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Stopwatch\Stopwatch;

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services();

    $services->alias( Profiler::class, 'profiler' );

    $services->set( ProfilerBar::class )
        ->autowire()
        ->tag( 'kernel.event_listener' );

    $container->services()
            // ->set( Clerk::class )
            // ->args(
            //     [
            //         service( Stopwatch::class ),
            //         service( 'logger' ),
            //         param( 'kernel.debug' ), // enabled when debugging, regardless of env
            //     ],
            // )

            // TelemetryEventSubscriber
            // ->set( ClerkProfiler::class )
            // ->tag( 'kernel.event_subscriber' )
            // ->args( [service( Clerk::class )] )

            // Easy links for navigating the _dev stage
        ->set( DevHotlinks::class )
        ->tag( 'data_collector' )
            //
        ->set( PipelineCollector::class )
        ->tag( 'data_collector' )
            //
        ->set( ParameterSettingsCollector::class )
        ->args(
            [
                service( 'parameter_bag' ),
                // service( Settings::class )
            ],
        )
        ->tag(
            'data_collector',
            [
                'template' => '@Core/profiler/parameter_settings.html.twig',
                'priority' => 240,
            ],
        );
};
