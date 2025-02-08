<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;
use Core\View\{Document, DocumentView, Latte\ViewComponentExtension, Parameters, TemplateEngine};

return static function( ContainerConfigurator $container ) : void {
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
