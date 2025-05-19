<?php

declare(strict_types=1);

namespace Core\Framework\Config;

use Core\Autowire\SettingsAccessor;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\{Route, RouteCollection};
use RuntimeException;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html
 */
abstract class RouteLoader extends Loader
{
    use SettingsAccessor;

    private bool $isLoaded = false;

    protected readonly RouteCollection $routes;

    #[Required]
    final public function __construct(
        #[Autowire( param : 'kernel.environment' )]
        ?string $_env,
    ) {
        parent::__construct( $_env );
    }

    /**
     * @return class-string|false
     */
    abstract public function controller() : string|false;

    abstract public function type() : string;

    abstract protected function configure( mixed $resource, ?string $type ) : void;

    /**
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

        $this->routes ??= new RouteCollection();

        $this
            ->importController( $this->controller() )
            ->configure( $resource, $type );

        $this->isLoaded = true;

        return $this->routes;
    }

    /**
     * @param class-string|false $controller
     *
     * @return self
     */
    private function importController( false|string $controller ) : self
    {
        if ( $controller === false ) {
            return $this;
        }

        if ( \class_exists( $controller ) ) {
            $this->routes->addCollection(
                $this->routeCollection( $controller ),
            );
            $this->routes->setHost(
                pattern      : '{_host}',
                requirements : ['_host' => '.+'],
            );
        }
        else {
            $message = "The controller '{$controller}' does not exists.";
            throw new InvalidArgumentException( $message );
        }

        return $this;
    }

    final protected function name( string $name ) : self
    {
        $this->routes->addNamePrefix( \trim( $name, " \n\r\t\v\0." ).'.' );
        return $this;
    }

    /**
     * @param string               $string
     * @param array<string,string> $defaults
     * @param array<string,string> $requirements
     *
     * @return $this
     */
    final protected function path(
        string $string,
        array  $defaults = [],
        array  $requirements = [],
    ) : self {
        $this->routes->addPrefix( $string, $defaults, $requirements );
        return $this;
    }

    /**
     * @param string               $pattern
     * @param array<string,string> $defaults
     * @param array<string,string> $requirements
     *
     * @return $this
     */
    final protected function host(
        string $pattern,
        array  $defaults = [],
        array  $requirements = [],
    ) : self {
        $this->routes->setHost( $pattern, $defaults, $requirements );
        return $this;
    }

    final protected function scheme( string ...$scheme ) : self
    {
        $this->routes->setSchemes( $scheme );

        return $this;
    }

    final protected function method( string ...$method ) : self
    {
        $this->routes->setMethods( $method );

        return $this;
    }

    final protected function add( string $name, Route $route, int $priority = 0 ) : void
    {
        $this->routes->add( $name, $route, $priority );
    }

    public function supports( $resource, ?string $type = null ) : bool
    {
        return $type === $this->type();
    }

    /**
     * @param mixed  $from
     * @param string $type
     *
     * @return RouteCollection
     */
    public function routeCollection(
        mixed  $from,
        string $type = 'attribute',
    ) : RouteCollection {
        $import = $this->import( $from, $type );

        \assert( $import instanceof RouteCollection );

        return $import;
    }
}
