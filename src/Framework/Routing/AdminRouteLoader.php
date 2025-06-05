<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Container\RouteLoader;
use Core\Controller\AdminController;

final class AdminRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'admin';
    }

    public function controller() : string
    {
        return AdminController::class;
    }

    protected function configure( mixed $resource, ?string $type ) : void
    {
        if ( $this->getSetting( 'admin.access.sub_domain', true ) ) {
            $this
                ->path( '/' )
                ->host(
                    pattern      : 'admin.{_host}',
                    requirements : ['_host' => '.+'],
                );
        }
        else {
            $this->path( '/admin' );
        }

        $this->name( 'admin' )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
