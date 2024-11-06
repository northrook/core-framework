<?php

namespace Core\Framework\DependencyInjection;

use Core\Framework\Exception\ServiceInjectionException;

/**
 * @phpstan-require-implements AutowireServicesInterface
 */
trait AutowireServices
{
    /**
     * Return an array of `[propertyName => Service::class]`.
     *
     * - The {@see ComponentFactory} will autowire services registered in the {@see ServiceContainer}.
     *
     * @return array<string, class-string>
     */
    abstract public function getAutowireServices() : array;

    /**
     * @param string $property
     * @param object $service
     *
     * @return void
     */
    final public function setAutowireService( string $property, object $service ) : void
    {
        if ( ! \property_exists( $this, $property ) ) {
            throw new ServiceInjectionException( $property, $service::class );
        }
        $this->{$property} = $service;
    }
}
