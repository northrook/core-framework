<?php

// -------------------------------------------------------------------
// config\framework\application
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Events\RequestAttributeHandler;

return static function( ContainerConfigurator $container ) : void {
    $container->services()->set( RequestAttributeHandler::class )
        ->tag( 'kernel.event_listener' );
};
