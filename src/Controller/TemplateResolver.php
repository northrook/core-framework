<?php

namespace Core\Framework\Controller;

use ReflectionClass;
use ReflectionMethod;

/**
 * @template T of object
 */
final readonly class TemplateResolver
{
    /** @var ReflectionClass<T> */
    public ReflectionClass $reflectClass;

    public ReflectionMethod $reflectMethod;

    /**
     * @param class-string<T> $controller
     * @param string          $method
     */
    public function __construct( string $controller, string $method )
    {
        \assert( \class_exists( $controller ) && \method_exists( $controller, $method ) );

        $this->reflectClass = new ReflectionClass( $controller );
        $this->reflectMethod = new ReflectionMethod( $controller, $method );

    }
}
