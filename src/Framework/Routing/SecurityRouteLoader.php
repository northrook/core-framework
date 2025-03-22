<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\{AssetController};
use Symfony\Component\Routing\Route;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class SecurityRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'assets';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return AssetController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : bool
    {
        $this
            ->name( 'assets' )
            ->path( '/assets/' )
            ->scheme( 'https' )
            ->method( 'GET' );

        dump( $this->routes );
        return true;
    }
}
