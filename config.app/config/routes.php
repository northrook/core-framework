<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Core\Controller\{
    PublicController,
    AdminController,
    SecurityController,
    AssetController,
    SystemController,
};

return static function( RoutingConfigurator $routes ) : void {
    $routes->import(
        resource : [
            'path'      => '../src/Controller/',
            'namespace' => 'App\Controller',
        ],
        type     : 'attribute',
    );
    $routes->import(
        resource : PublicController::class,
        type     : 'public',
    );
    $routes->import(
        resource : AdminController::class,
        type     : 'admin',
    );
    $routes->import(
        resource : SecurityController::class,
        type     : 'security',
    );
    $routes->import(
        resource : AssetController::class,
        type     : 'assets',
    );
    $routes->import(
        resource : SystemController::class,
        type     : 'system',
    );
};
