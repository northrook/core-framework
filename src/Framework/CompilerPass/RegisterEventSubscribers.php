<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Symfony\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;

/**
 * @internal
 */
#[Deprecated]
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

        foreach ( $this->taggedServiceIds( 'kernel.event_subscriber', 'kernel.event_listener' ) as $id ) {
            $definition = $this->container->getDefinition( $id );

            $class = $definition->getClass() ?? throw new InvalidArgumentException(
                $id.' does not have an referenced class.',
            );

            if ( ! \class_exists( $class ) ) {
                $this->console->error( __METHOD__." {$id} {$class} does not exist" );
            }

            if ( \is_subclass_of( $class, ControllerAwareEvent::class ) ) {
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
