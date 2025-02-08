<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Assets\{AssetFactory, AssetManager};
use Core\Framework\ResponseView;
use Core\Interface\IconProviderInterface;
use Core\Pathfinder;
use Core\View\{ComponentFactory,
    ComponentFactory\ComponentBag,
    Document,
    DocumentView,
    IconSet,
    Latte\ViewComponentExtension,
    Parameters,
    TemplateEngine
};
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    // Component Service Locator
    $container->services()
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( CompilerPass::PLACEHOLDER_ARGS );

    $container->services()
        ->set( AssetManager::class )
        ->args(
            [
                service( AssetFactory::class ),
                null, // cache
                service( 'logger' ),
            ],
        )
        ->tag( 'core.service_locator' );

    $container->services()
        ->set( ResponseView::class )
        ->args(
            [
                service( DocumentView::class ),
                service( TemplateEngine::class ),
                service( ComponentFactory::class ),
                service( AssetManager::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'view'] )
        ->lazy();

    $container->services()
        ->set( IconSet::class )
        ->alias( IconProviderInterface::class, IconSet::class );

    $services = $container->services();
    // ->defaults()
    // ->autoconfigure();

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

    $view = $container->services()->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $view
        ->set( Document::class )
        ->arg( 0, service( 'logger' )->nullOnInvalid() )
        ->tag( 'controller.service_arguments' )
        ->tag( 'core.service_arguments' )
        ->autowire();

    $view
        ->set( DocumentView::class )
        ->args(
            [
                service( Document::class ),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
        ->lazy();
};
