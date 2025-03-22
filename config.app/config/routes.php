<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Core\Controller\{AdminController, AssetController, PublicController, SecurityController, SystemController};

return static function( RoutingConfigurator $routes ) : void {
    $appControllers = [
        'path'      => '../src/Controller/',
        'namespace' => 'App\Controller',
    ];

    $routes->import( $appControllers, 'attribute' );

    $routes->import( PublicController::class, 'public' );
    $routes->import( AdminController::class, 'admin' );
    $routes->import( SecurityController::class, 'security' );

    $routes->import( AssetController::class, 'assets' );
    // $routes->import( SystemController::class, 'system' );

    // $coreControllers = [
    //     'path'      => '@CoreBundle/src/Controller',
    //     'namespace' => 'Core\Controller',
    // ];
    // $routes->import( $coreControllers, 'attribute', exclude: 'AdminController' );
};
