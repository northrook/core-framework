<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function Support\uses_trait;

final class RegisterCoreServicesPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        dump( $container->findTaggedServiceIds( 'core.service_container' ) );

        $coreServiceLocator = $container->getDefinition( 'core.service_locator' );

        foreach ( \get_declared_classes() as $class ) {
            if ( uses_trait( $class, ServiceContainer::class ) && $container->hasDefinition( $class ) ) {
                $container->getDefinition( $class )->addMethodCall( 'setServiceLocator', [$coreServiceLocator] );
            }
        }
    }
}
