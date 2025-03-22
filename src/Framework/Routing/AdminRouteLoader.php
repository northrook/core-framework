<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Core\Controller\AdminController;
use Core\Interface\SettingsProviderInterface;
use Symfony\Component\Routing\Route;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
final class AdminRouteLoader extends RouteLoader
{
    public function __construct(
        ?string                                    $env,
        private readonly SettingsProviderInterface $settings,
    ) {
        parent::__construct( $env );
    }

    protected function routeCollection( mixed $resource, ?string $type ) : bool
    {
        if ( $this->settings->get( 'admin.access.sub_domain', true ) ) {
            $route = $this->subodomainRoute();
        }
        else {
            $route = $this->domainPathRoute();
        }

        $route->setDefault(
            '_controller',
            AdminController::class,
        );

        $route
            ->setSchemes( 'https' )
            ->setMethods( 'GET' );

        $this->routes->add( 'admin', $route );

        return true;
    }

    public function type() : string
    {
        return 'admin';
    }

    private function subodomainRoute() : Route
    {
        return new Route(
            path : '/',
            host : 'admin.{domain}.{tld}',
        );
    }

    private function domainPathRoute() : Route
    {
        return new Route(
            path : '/admin',
        );
    }
}
