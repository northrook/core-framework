<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document, DocumentView};

return static function( ContainerConfigurator $container ) : void {
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
