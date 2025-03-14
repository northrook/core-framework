<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\ToastService;
use Core\Framework\Events\{DeferredCachePoolHandler, RequestAttributeHandler, ToastMessageInjector};

return static function( ContainerConfigurator $container ) : void {
    $events = $container->services()->defaults();

    $events->set( RequestAttributeHandler::class )
        ->tag( 'kernel.event_listener' );

    $events->set( DeferredCachePoolHandler::class )
        ->args( [service( 'cache.asset_pool' )] )
        ->tag( 'kernel.event_listener' );

    $events->set( ToastMessageInjector::class )
        ->args( [service( ToastService::class )] )
        ->tag( 'kernel.event_subscriber' );
};
