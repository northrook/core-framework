<?php

namespace Core\Framework\DependencyInjection;

interface AutowireServicesInterface
{
    /**
     * Return an array of `[propertyName => Service::class]`.
     *
     * - The {@see ComponentFactory} will autowire services registered in the {@see ServiceContainer}.
     *
     * @return array<string, class-string>
     */
    public function getAutowireServices() : array;

    /**
     * @param string $property
     * @param object $service
     *
     * @return void
     */
    public function setAutowireService( string $property, object $service ) : void;
}
