<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document,
    DocumentEngine,
    ComponentFactory,
    ComponentFactory\ComponentBag,
    IconSet,
    Template\Engine,
    Template\StyleSystemExtension,
    Template\ViewComponentExtension
};
use Core\Interface\IconProviderInterface;
use const Support\{AUTO, PLACEHOLDER_ARGS};

return static function( ContainerConfigurator $container ) : void {
    //
    // $container->services()
    //     ->set( ResponseRenderer::class )
    //     ->args(
    //         [
    //             service( DocumentEngine::class ),
    //             service( TemplateEngine::class ),
    //             service( ComponentFactory::class ),
    //             service( AssetManager::class ),
    //             service( Document::class ),
    //             service( Parameters::class ),
    //             service( ToastService::class ),
    //         ],
    //     )
    //     ->tag( 'monolog.logger', ['channel' => 'view'] )
    //     ->lazy();
    //
    $container->services()
        ->set( IconSet::class )
        ->alias( IconProviderInterface::class, IconSet::class );
    //
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    // $services
    //     ->set( ViewComponentExtension::class )
    //     ->args(
    //         [
    //             service( ComponentFactory::class ),
    //             service( 'logger' )->nullOnInvalid(),
    //         ],
    //     );

    // $services
    //     ->set( Engine::class )
    //     ->args(
    //         [
    //             param( 'dir.cache.view' ),
    //             [
    //                 param( 'dir.templates' ),
    //                 param( 'dir.core.templates' ),
    //             ],
    //             [],
    //             param( 'kernel.default_locale' ),
    //             true, // preformatter
    //             true, // cache
    //             AUTO, // profiler
    //             AUTO, // logger
    //         ],
    //     )
    //     ->call( 'addExtension', [service( ViewComponentExtension::class )] )
    //     ->call( 'addExtension', [inline_service( StyleSystemExtension::class )] );

    // $services
    //     ->set( ComponentFactory::class )
    //     ->args(
    //         [
    //             '$engine'     => service( Engine::class ),
    //             '$locator'    => service( 'view.component_locator' ),
    //             '$components' => abstract_arg( ComponentBag::class ),
    //             '$tags'       => abstract_arg( 'ComponentProperties::tagged' ),
    //         ],
    //     )
    //     ->private(); // ->lazy()

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
