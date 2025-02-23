<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Symfony\DependencyInjection\CompilerPass;
use JetBrains\PhpStorm\Deprecated;
use Psr\Log\LoggerAwareInterface;
use ReflectionClass;
use Throwable;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

/**
 * @internal
 */
final class AutowwireAwareDepdendencies extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        $this->registerLoggerAwareServices();
    }

    private function registerLoggerAwareServices() : void
    {
        foreach ( $this->container->getDefinitions() as $definition ) {
            $class = $definition->getClass();

            if ( $class && \is_subclass_of( $class, LoggerAwareInterface::class ) ) {
                try {
                    $reflect = new ReflectionClass( $class );
                }
                catch ( Throwable $e ) {
                    $this->console->error( $e->getMessage() );

                    continue;
                }
                $setLoggerMethod = $reflect->getMethod( 'setLogger' );
                $docBlock        = $setLoggerMethod->getDocComment();

                if ( $docBlock && \str_contains( $docBlock, '@deprecated' ) ) {
                    $this->console->warning( 'setLogger deprecated for: '.$class );

                    continue;
                }

                $deprecatedAttribute = $reflect->getAttributes( Deprecated::class );

                if ( $deprecatedAttribute ) {
                    $this->console->warning( 'setLogger deprecated for: '.$class );

                    continue;
                }

                $definition->addMethodCall( 'setLogger', [new Reference( 'logger' )] );
            }
        }
    }
}
