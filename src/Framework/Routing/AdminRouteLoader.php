<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AdminController;
use Core\Framework\Config\RouteLoader;

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

    protected function compile( mixed $resource, ?string $type ) : void
    {
        if ( $this->settings->get( 'admin.access.sub_domain', true ) ) {
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
