<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\SecurityController;
use Core\Framework\Config\RouteLoader;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class SecurityRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'security';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return SecurityController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this
            ->name( 'security' )
            ->scheme( 'https' )
            ->method( 'GET', 'POST' );
    }
}
