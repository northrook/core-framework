<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Interface\IconProviderInterface;
use Core\View\{Document, DocumentEngine, IconSet};

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( IconSet::class )
        ->alias( IconProviderInterface::class, IconSet::class );
    //
    $services = $container->services()
        ->defaults()
        ->tag( 'monolog.logger', ['channel' => 'view'] );

    $services
        ->set( DocumentEngine::class )
        ->args( [service( Document::class )] )
        ->lazy();
};
