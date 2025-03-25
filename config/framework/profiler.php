<?php

// -------------------------------------------------------------------
// config\framework\profiler
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Profiler\{DevHotlinks, ParameterSettingsCollector, PipelineCollector, ProfilerBar};
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpKernel\Profiler\Profiler;

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services();

    $services->alias( Profiler::class, 'profiler' );
    $services->alias( WebDebugToolbarListener::class, 'web_profiler.debug_toolbar' );

    $services->set( ProfilerBar::class )
        ->autowire()
        ->tag( 'kernel.event_listener' );

    $services
            // Easy links for navigating the _dev stage
        ->set( DevHotlinks::class )
        ->tag( 'data_collector' );
    //
    $services
        ->set( PipelineCollector::class )
        ->tag( 'data_collector' );
    //
    $services->set( ParameterSettingsCollector::class )
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
