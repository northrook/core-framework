<?php

// -------------------------------------------------------------------
// config\framework\lifecycle
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Lifecycle\ResponseHandler;

return static function( ContainerConfigurator $container ) : void {

    $container->services()

            // Response EventSubscriber;
        ->set( ResponseHandler::class )
        ->tag( 'kernel.event_subscriber' );
};
