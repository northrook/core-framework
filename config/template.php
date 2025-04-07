<?php

// -------------------------------------------------------------------
// config\framework\template
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\ComponentFactory\ComponentBag;
use Core\View\Template\ViewRenderExtension;
use Core\View\ViewFactory;
use Random\Engine;
use const Support\{AUTO, PLACEHOLDER_ARGS, PLACEHOLDER_ARRAY};

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );
    /**
     * Engines
     */
    $services->set( 'core.view.engine', Engine::class )
        ->args(
            [
                // Cache Directory
                '%kernel.cache_dir%/view',
                // Template directories
                ['%dir.root%/templates', '%dir.core%/templates'],
                // Template strings
                PLACEHOLDER_ARRAY, // [name => template]
                '%kernel.default_locale%',
                true, // preformatter
                true, // cache
                AUTO, // profiler
                AUTO, // logger
            ],
        )
        ->call( 'addExtension', [service( ViewRenderExtension::class )] )
        ->alias( Engine::class, 'core.view.engine' );

    $services->set( 'core.view.factory.engine', Engine::class )
        ->args(
            [
                // Cache Directory
                '%kernel.cache_dir%/view/components',
                // Template directories
                ['%dir.root%/templates', '%dir.core%/templates'],
                // Template strings
                PLACEHOLDER_ARRAY, // [name => template]
                '%kernel.default_locale%',
                true, // preformatter
                true, // cache
                AUTO, // profiler
                AUTO, // logger
            ],
        );

    /**
     * Factories
     */
    $services
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );

    $services->set( ViewFactory::class )
        ->args(
            [
                '$engine'     => service( 'core.view.factory.engine' ),
                '$locator'    => service( 'view.component_locator' ),
                '$components' => abstract_arg( ComponentBag::class ),
                '$tags'       => abstract_arg( 'ComponentProperties::tagged' ),
            ],
        );

    $services
        ->set( ViewRenderExtension::class )
        ->args( [service( ViewFactory::class )] );
};
