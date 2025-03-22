<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AdminController;
use Core\Framework\Config\RouteLoader;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class AdminRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'admin';
    }

    // @phpstan-ignore-next-line
    public function controller() : string|false
    {
        return AdminController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : void
    {
        if ( $this->settings->get( 'admin.access.sub_domain', true ) ) {
            $this
                ->path( '/' )
                ->host( 'admin.{domain}.{tld}' );
        }
        else {
            $this->path( '/admin' );
        }

        $this->name( 'admin' )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
