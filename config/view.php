<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document, DocumentEngine, IconProviderService, ViewFactory};

return static function( ContainerConfigurator $container ) : void {
    // $container->services()
    //     ->set( ViewFactory::class );

    //
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $services
        ->set( IconProviderService::class )
        ->args(
            [
                service( 'cache.asset_pool' ),
                service( 'logger' )->nullOnInvalid(),
            ],
        );

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
