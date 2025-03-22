<?php

declare(strict_types=1);

namespace Core\Framework\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\{Route, RouteCollection};
use RuntimeException;

#[AutoconfigureTag( 'routing.loader' )]
abstract class RouteLoader extends Loader
{
    private bool $isLoaded = false;

    protected readonly RouteCollection $routes;

    abstract public function type() : string;

    abstract protected function routeCollection( mixed $resource, ?string $type ) : bool;

    /**
     * @see https://symfony.com/doc/current/routing/custom_route_loader.html#creating-a-custom-loader
     *
     * @param mixed       $resource
     * @param null|string $type
     *
     * @return RouteCollection
     */
    final public function load( mixed $resource, ?string $type = null ) : RouteCollection
    {
        if ( $this->isLoaded === true ) {
            $message = "The '".$this::class."' RouteLoader is already loaded";
            throw new RuntimeException( $message );
        }

        $this->routes = new RouteCollection();

        $this->isLoaded = $this->routeCollection( $resource, $type );

        return $this->routes;
    }

    final protected function add( string $name, Route $route, int $priority = 0 ) : void
    {
        $this->routes->add( $name, $route, $priority );
    }

    public function supports( $resource, ?string $type = null ) : bool
    {
        return $type === $this->type();
    }
}
