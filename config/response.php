<?php

// -------------------------------------------------------------------
// config\framework\response
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Response\{Headers, Parameters};
use Core\View\Document;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( Headers::class )
        ->args( [service( 'request_stack' )] );

    $container->services()
        ->set( Parameters::class );

    $container->services()
        ->set( Document::class )
        ->args( [service( 'logger' )->nullOnInvalid()] );
};
