<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class AdminRouteLoader extends RouteLoader
{
    protected function routeCollection( mixed $resource, ?string $type ) : bool
    {
        return true;
    }

    public function type() : string
    {
        return 'admin';
    }
}
