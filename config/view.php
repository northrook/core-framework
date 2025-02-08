<?php

// -------------------------------------------------------------------
// config\framework\view
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Document, DocumentView};

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services()
        ->set( Document::class )
        ->arg( 0, service( 'logger' ) )
        ->tag( 'controller.service_arguments' )
        ->tag( 'core.service_arguments' )
        ->autowire()
            //
            //
        ->set( DocumentView::class );
};
