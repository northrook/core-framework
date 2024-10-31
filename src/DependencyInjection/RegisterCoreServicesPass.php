<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterCoreServicesPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        dump( $container->findTaggedServiceIds( 'core.service_locator' ) );
        dump( $container->findTaggedServiceIds( 'core.service_container' ) );
    }
}
