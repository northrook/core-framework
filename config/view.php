<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{AssetManager, Pathfinder};
use Core\Framework\ResponseRenderer;
use Core\Framework\Service\ToastService;
use Core\View\{ComponentFactory,
    ComponentFactory\ComponentBag,
    Document,
    DocumentEngine,
    IconSet,
    Interface\IconProviderInterface,
    Latte\ViewComponentExtension,
    Parameters,
    TemplateEngine
};
use const Support\PLACEHOLDER_ARGS;

return static function( ContainerConfigurator $container ) : void {
    //
    // Component Service Locator
    $container->services()
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( PLACEHOLDER_ARGS );
    //
    $container->services()
        ->set( ResponseRenderer::class )
        ->args(
            [
                service( DocumentEngine::class ),
                service( TemplateEngine::class ),
                service( ComponentFactory::class ),
                service( AssetManager::class ),
                service( Document::class ),
                service( ToastService::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'view'] )
        ->lazy();
    //
    $container->services()
        ->set( IconSet::class )
        ->alias( IconProviderInterface::class, IconSet::class );
    //
    $services = $container->services();
    // ->defaults()
    // ->autoconfigure();
    //
    // IconSet
    $services
        ->set( ComponentFactory::class )
        ->args(
            [
                '$locator'    => service( 'view.component_locator' ),
                '$components' => abstract_arg( ComponentBag::class ),
                '$tags'       => abstract_arg( 'ComponentProperties::tagged' ),
                '$logger'     => service( 'logger' )->nullOnInvalid(),
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

    $container->services()
            //
        ->set( TemplateEngine::class )
        ->tag( 'core.service_arguments' )
        ->args(
            [
                param( 'dir.cache.view' ),
                service( Parameters::class ),
                service( Pathfinder::class ),
                service( 'logger' ),
                [
                    param( 'dir.templates' ),
                    param( 'dir.core.templates' ),
                ],
                [service( ViewComponentExtension::class )],
                param( 'kernel.default_locale' ),
                param( 'kernel.debug' ),
            ],
        );

    $view = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $view
        ->set( Document::class )
        ->arg( 0, service( 'logger' )->nullOnInvalid() )
        ->tag( 'controller.service_arguments' )
        ->tag( 'core.service_arguments' )
        ->autowire();

    $view
        ->set( DocumentEngine::class )
        ->args(
            [
                service( Document::class ),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
        ->lazy();
};
