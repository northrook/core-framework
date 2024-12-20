<?php

// -------------------------------------------------------------------
// config\framework\response
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Response\{Headers, Parameters};
use Core\Framework\Response\{Document};

return static function( ContainerConfigurator $container ) : void {

    $container->services()->defaults()
        ->tag( 'controller.service_arguments' )
        ->autowire()

            // ResponseHeaderBag Service
        ->set( Headers::class )

            // Document Properties
        ->set( Document::class )

            // Template Parameters
        ->set( Parameters::class );
};
