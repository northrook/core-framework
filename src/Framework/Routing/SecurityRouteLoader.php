<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Container\RouteLoader;
use Core\Controller\SecurityController;

final class SecurityRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'security';
    }

    public function controller() : string
    {
        return SecurityController::class;
    }

    protected function configure( mixed $resource, ?string $type ) : void
    {
        $this
            ->name( 'security' )
            ->scheme( 'https' )
            ->method( 'GET', 'POST' );
    }
}
