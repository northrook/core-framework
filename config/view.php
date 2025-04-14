<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document, DocumentEngine, IconProviderService};

return static function( ContainerConfigurator $container ) : void {
    //
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $services
        ->set( IconProviderService::class );

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
