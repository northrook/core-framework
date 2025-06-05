<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Container\RouteLoader;
use Core\Controller\SystemController;

final class SystemRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'system';
    }

    public function controller() : string
    {
        return SystemController::class;
    }

    protected function configure( mixed $resource, ?string $type ) : void
    {
        $this
            ->scheme( 'https' )
            ->method( 'GET', 'POST' );
    }
}
