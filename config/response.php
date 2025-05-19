<?php

// -------------------------------------------------------------------
// config\framework\response
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\CompilerPass\AutowireServiceArguments;
use Core\Framework\Response\{Headers, Parameters};
use Core\View\Document;

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services()
        ->defaults()
        ->tag( AutowireServiceArguments::LOCATOR );

    $services
        ->set( Headers::class )
        ->args( [service( 'request_stack' )] );

    $services
        ->set( Parameters::class );

    $services
        ->set( Document::class )
        ->args( [service( 'logger' )->nullOnInvalid()] );
};
