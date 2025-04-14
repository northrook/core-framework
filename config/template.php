<?php

// -------------------------------------------------------------------
// config\framework\template
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\Template\{Engine, IconProviderExtension, ViewComponentExtension};
use Core\View\{ComponentFactory, IconProviderService, IconSet};
use Core\View\ComponentFactory\ComponentBag;
use const Support\{AUTO, PLACEHOLDER_ARGS, PLACEHOLDER_ARRAY};

return static function( ContainerConfigurator $container ) : void {
    // Component Service Locator
    $container->services()
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );

    $templateDirectories = [
        'app'  => param( 'dir.templates' ),
        'core' => param( 'dir.core.templates' ),
    ];
    // Template strings [name => template]
    $preloadedTemplates = PLACEHOLDER_ARRAY;

    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );
    /**
     * Engines
     */
    $services->set( 'core.view.engine', Engine::class )
        ->args(
            [
                param( 'dir.cache.view' ),
                $templateDirectories,
                $preloadedTemplates,
                '%kernel.default_locale%',
                true, // preformatter
                true, // cache
                AUTO, // profiler
                AUTO, // logger
            ],
        )
        ->call( 'addExtension', [service( ViewComponentExtension::class )] )
        ->alias( Engine::class, 'core.view.engine' );

    /**
     * Factories
     */
    $services->set( ComponentFactory::class )
        ->args(
            [
                '$engine'     => service( 'core.view.engine' ),
                '$locator'    => service( 'view.component_locator' ),
                '$components' => abstract_arg( ComponentBag::class ),
                '$tags'       => abstract_arg( 'ComponentProperties::tagged' ),
            ],
        );

    $services
        ->set( ViewComponentExtension::class )
        ->args( [service( ComponentFactory::class )] );

    $services
            ->set( IconProviderExtension::class )
            ->args( [service( IconProviderService::class )] );
};
