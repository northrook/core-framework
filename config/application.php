<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\ToastService;
use Core\Framework\Events\{RequestAttributeHandler, ToastMessageInjector};

return static function( ContainerConfigurator $container ) : void {
    $events = $container->services()->defaults()
        ->tag( 'kernel.event_listener' );

    $events->set( RequestAttributeHandler::class );

    $events->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] );
};
