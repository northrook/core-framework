<?php

// -------------------------------------------------------------------
// config\framework\http
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $controller ) : void {
    $controller->services()
        ->set( 'cache.core.http_event', PhpFilesAdapter::class )
        ->args(
            [
                'http_event',  // $namespace
                0,             // $defaultLifetime
                '%dir.cache%', // $directory
                true,          // $appendOnly
            ],
        )
        ->tag( 'cache.pool' );
};
