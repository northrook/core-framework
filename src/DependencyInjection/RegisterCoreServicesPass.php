<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};
use function Support\uses_trait;
use CompileError;

final class RegisterCoreServicesPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( 'core.service_locator' ) ) {
            return;
        }

        $this->registerTaggedServices( $container );
        $this->injectServiceLocator( $container );
    }

    private function registerTaggedServices( ContainerBuilder $container ) : void
    {

        $serviceLocatorArguments = $container->getDefinition( 'core.service_locator' )->getArguments()[0] ?? [];

        foreach ( $container->findTaggedServiceIds( 'core.service_locator' ) as $id => $unused ) {
            $taggedService = $container->getDefinition( $id );
            $serviceId     = $taggedService->innerServiceId ?? $taggedService->getClass();
            if ( ! $serviceId ) {
                throw new CompileError( $this::class." could not find a serviceId for '{$id}' when parsing services tagged with 'core.service_locator'." );
            }
            $serviceLocatorArguments[$id] = new Reference( $serviceId );
        }

        $container->getDefinition( 'core.service_locator' )->setArguments( [$serviceLocatorArguments] );
    }


    private function injectServiceLocator( ContainerBuilder $container ) : void
    {
        foreach ( \get_declared_classes() as $class ) {
            if ( uses_trait( $class, ServiceContainer::class ) && $container->hasDefinition( $class ) ) {
                $container->getDefinition( $class )->addMethodCall( 'setServiceLocator', [$container->getDefinition( 'core.service_locator' )] );
            }
        }
    }

}
