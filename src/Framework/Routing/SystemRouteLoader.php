<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\{SystemController};
use Core\Framework\Config\RouteLoader;

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
