<?php

// -------------------------------------------------------------------
// config\framework\routes
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Core\Controller\{AdminController, AssetController, PublicController, SecurityController};

return static function( RoutingConfigurator $routes ) : void {
    $routes->import( PublicController::class, 'public' );
    $routes->import( AdminController::class, 'admin' );
    $routes->import( SecurityController::class, 'security' );
    $routes->import( AssetController::class, 'assets' );
};
