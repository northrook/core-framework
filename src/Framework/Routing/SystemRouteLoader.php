<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\{SystemController};
use Core\Framework\Config\RouteLoader;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class SystemRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'system';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return SystemController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this
            ->scheme( 'https' )
            ->method( 'GET', 'POST' );
    }
}
