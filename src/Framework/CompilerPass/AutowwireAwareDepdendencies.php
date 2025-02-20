<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Symfony\DependencyInjection\CompilerPass;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

/**
 * @internal
 */
final class AutowwireAwareDepdendencies extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        foreach ( $this->container->getDefinitions() as $definition ) {
            $class = $definition->getClass();

            if ( $class && \is_subclass_of( $class, LoggerAwareInterface::class ) ) {
                $definition->addMethodCall( 'setLogger', [new Reference( 'logger' )] );
            }
        }
    }
}
