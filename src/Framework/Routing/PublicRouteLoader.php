<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\{PublicController};
use Symfony\Component\Routing\Route;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class PublicRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'admin';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return PublicController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : bool
    {
        $this->name( 'public' )
            ->path( '/' )
            ->scheme( 'https' )
            ->method( 'GET' );

        dump( $this->routes );
        return true;
    }
}
