<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document, DocumentEngine, IconProviderService, ViewFactory};

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( ViewFactory::class )
        ->args(
            [
                '$engine'  => service( 'core.view.engine' ),
                '$locator' => service( 'view.component_locator' ),
            ],
        );

    //
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $services
        ->set( IconProviderService::class )
        ->args( [service( 'cache.asset_pool' )] );

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
