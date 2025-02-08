<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Symfony\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};
use Core\Framework\Controller\ControllerEventSubscriber;
use InvalidArgumentException;

/**
 * @internal
 */
final class RegisterEventSubscribers extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        if ( $container->has( 'event_dispatcher' ) ) {
            $this->controllerEventSubscriber();
        }
    }

    private function controllerEventSubscriber() : void
    {
        $dispatcher = $this->container->findDefinition( 'event_dispatcher' );

        foreach ( $this->container->findTaggedServiceIds( 'kernel.event_subscriber' ) as $id => $tags ) {
            $definition = $this->container->getDefinition( $id );

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
                        [new Reference( $id ), 'validateRequestController'],
                        128,
                    ],
                );
            }
        }
    }
}
