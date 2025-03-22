<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AssetController;
use Core\Framework\Config\RouteLoader;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class AssetsRouteLoader extends RouteLoader
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

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this
            ->name( 'assets' )
            ->path( '/assets/' )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
