<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\SecurityController;
use Core\Framework\Config\RouteLoader;

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

    protected function compile( mixed $resource, ?string $type ) : void
    {
        $this
            ->name( 'security' )
            ->scheme( 'https' )
            ->method( 'GET', 'POST' );
    }
}
