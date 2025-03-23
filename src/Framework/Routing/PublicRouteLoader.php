<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\PublicController;
use Core\Framework\Config\RouteLoader;

final class PublicRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'public';
    }

    public function controller() : string
    {
        return PublicController::class;
    }

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this->name( 'public' )
            ->path( '/' )
            ->host(
                pattern      : '{_locale}{_host}',
                defaults     : ['_locale' => ''],
                requirements : [
                    '_locale' => '[a-z]*\.|',
                    '_host'   => '.+',
                ],
            )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
