<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AssetController;
use Core\Framework\Config\RouteLoader;

final class AssetsRouteLoader extends RouteLoader
{
    public function type() : string
    {
        return 'assets';
    }

    public function controller() : string
    {
        return AssetController::class;
    }

    protected function configure( mixed $resource, ?string $type ) : void
    {
        $this
            ->name( 'assets' )
            ->path( '/' )
            ->scheme( 'https' )
            ->method( 'GET' );
    }
}
