<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{
    Document,
    DocumentEngine,
    TemplateEngine,
    ComponentFactory,
    ComponentFactory\ComponentBag,
    IconSet,
    Latte\ViewComponentExtension,
};
use Core\Interface\IconProviderInterface;
use Core\Pathfinder;
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
    //
    // Component Service Locator
    $container->services()
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );
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

    // IconSet
    $services
        ->set( ComponentFactory::class )
        ->args(
            [
                '$locator'    => service( 'view.component_locator' ),
                '$components' => abstract_arg( ComponentBag::class ),
                '$tags'       => abstract_arg( 'ComponentProperties::tagged' ),
            ],
        )
        ->private(); // ->lazy()

    $services
        ->set( ViewComponentExtension::class )
        ->args(
            [
                service( ComponentFactory::class ),
                service( 'logger' )->nullOnInvalid(),
            ],
        );

    $services
        ->set( TemplateEngine::class )
        ->args(
            [
                param( 'dir.cache.view' ),
                service( Pathfinder::class ),
                [
                    param( 'dir.templates' ),
                    param( 'dir.core.templates' ),
                ],
                [service( ViewComponentExtension::class )],
                param( 'kernel.default_locale' ),
                param( 'kernel.debug' ),
            ],
        );

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
