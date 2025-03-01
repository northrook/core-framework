<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\ToastService;
use Core\Framework\Events\{RequestAttributeHandler, ToastMessageInjector};

return static function( ContainerConfigurator $container ) : void {
    $events = $container->services()->defaults();

    $events->set( RequestAttributeHandler::class )
        ->tag( 'kernel.event_listener' );

    $events->set( ToastMessageInjector::class )
        ->tag( 'kernel.event_subscriber' )
        ->args( [service( ToastService::class )] );
};
