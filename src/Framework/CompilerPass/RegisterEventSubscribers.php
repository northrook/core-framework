<?php

namespace Core\Framework\CompilerPass;

use Core\Framework\ControllerEventSubscriber;
use Core\Symfony\DependencyInjection\CompilerPass;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

/**
 * @internal
 */
final class RegisterEventSubscribers extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->has( 'event_dispatcher' ) ) {
            return;
        }

        $dispatcher = $container->findDefinition( 'event_dispatcher' );

        foreach ( $container->findTaggedServiceIds( 'kernel.event_subscriber' ) as $id => $tags ) {
            $definition = $container->getDefinition( $id );

            $class = $definition->getClass() ?? throw new InvalidArgumentException(
                $id.' does not have an referenced class.',
            );

            if ( ! \class_exists( $class ) ) {
                $this->console->error( __METHOD__." {$id} {$class} does not exist" );
            }

            if ( \is_subclass_of( $class, ControllerEventSubscriber::class ) ) {
                $dispatcher->addMethodCall(
                    'addListener',
                    [
                        'kernel.controller',
                        [
                            new Reference( $id ),
                            'validateRequestController',
                        ],
                    ],
                );
            }
        }
    }
}
