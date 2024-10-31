<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterCoreServicesPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        dump( $container->getDefinition( 'core.service_locator' ) );
        dump( $container->findTaggedServiceIds( 'core.service_container' ) );

        // $implementsServiceContainerInterface = get_declared_classes();

        foreach ( \get_declared_classes() as $class ) {
            if ( \is_subclass_of( $class, ServiceContainerInterface::class ) ) {
                dump( '::: DI :::' );
                dump( $class );
                if ( $container->hasDefinition( $class ) ) {
                    dump( $container->getDefinition( $class ) );
                }
            }
        }

        // dump( $implementsServiceContainerInterface );
    }
}
