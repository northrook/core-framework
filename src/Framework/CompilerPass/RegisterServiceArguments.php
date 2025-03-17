<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition, Reference};
use Core\Framework\Controller;
use Core\Symfony\Console\{ListReport};
use Core\Symfony\DependencyInjection\CompilerPass;
use Core\Symfony\Interface\ServiceContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Support\class_adopts_any;

/**
 * @internal
 */
final class RegisterServiceArguments extends CompilerPass
{
    public const string TAG = 'core.service_arguments';

    protected readonly Definition $serviceLocator;

    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( $this::TAG ) ) {
            $service_argument = $this::TAG;
            $this->console->error(
                $this::class." cannot find required '{$service_argument}' definition.",
            );
            return;
        }

        $this->serviceLocator = $container->getDefinition( $this::TAG );

        $this
            ->registerTaggedServices()
            ->injectServiceLocator();
    }

    private function registerTaggedServices() : self
    {
        $arguments = $this->serviceLocator->getArguments()[0] ?? [];

        foreach ( $this->taggedServiceIds( $this::TAG, 'controller.service_arguments' ) as $id ) {
            $taggedService = $this->container->getDefinition( $id );
            $serviceId     = $taggedService->innerServiceId ?? $taggedService->getClass();
            $serviceClass  = $taggedService->getClass();

            \assert( \is_string( $serviceClass ) && \class_exists( $serviceClass, false ) );

            if ( class_adopts_any( $serviceClass, AbstractController::class, Controller::class ) ) {
                continue;
            }

            if ( $namespaced = \strrpos( $serviceClass, '\\' ) ) {
                $namespace = \substr( $serviceClass, 0, $namespaced );
                $classname = \substr( $serviceClass, $namespaced + 1 );

                if ( $classname === 'Kernel'
                     || \str_ends_with( $namespace, 'Controller' )
                     || \str_ends_with( $classname, 'Controller' )
                ) {
                    continue;
                }
            }

            if ( $serviceId ) {
                $arguments[$id] = new Reference( $serviceId );
            }
            else {
                $service_argument = $this::TAG;
                $this->console->error(
                    $this::class." could not find a serviceId for '{$id}' when parsing services tagged with '{$service_argument}'.",
                );
            }
        }

        $this->serviceLocator->setArguments( [$arguments] );

        return $this;
    }

    private function injectServiceLocator() : void
    {
        $registeredServices = new ListReport( __METHOD__ );

        foreach ( $this->getDeclaredClasses() as $class ) {
            if (
                \is_subclass_of( $class, ServiceContainerInterface::class )
                && $this->container->hasDefinition( $class )
            ) {
                $registeredServices->item( $class );
                $this->container->getDefinition( $class )
                    ->addMethodCall(
                        'setServiceLocator',
                        [$this->serviceLocator],
                    );
            }
        }

        $registeredServices->output();
    }
}
