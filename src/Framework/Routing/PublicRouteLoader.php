<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\PublicController;
use Core\Framework\Config\RouteLoader;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class PublicRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'public';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return PublicController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this->name( 'public' )
            ->path( '/' )
            ->host( '{_locale}.{domain}.{tld}' )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
