<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition, Reference};
use Core\Autowire\{ServiceLocator, SettingsAccessor};
use Core\Framework\Controller;
use Core\Symfony\Console\{ListReport};
use Core\Symfony\DependencyInjection\{CompilerPass};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Support\{class_adopts_any, uses_trait};
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @internal
 */
final class RegisterServiceArguments extends CompilerPass
{
    public const string TAG = 'core.service_arguments';

    protected readonly Definition $serviceLocator;

    protected readonly Definition $settingsProvider;

    public function compile( ContainerBuilder $container ) : void
    {
        $this->serviceLocator   = $this->getDefinition( $this::TAG );
        $this->settingsProvider = $this->getDefinition( service( 'core.settings_provider' ) );

        $this
            ->registerTaggedServices()
            ->injectServices();
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

    private function injectServices() : void
    {
        $report = new ListReport( __METHOD__ );

        foreach ( $this->getDeclaredClasses() as $class ) {
            $definition = $this->getDefinition(
                id       : $class,
                nullable : true,
            );

            if ( ! $definition ) {
                continue;
            }

            $add = [];

            if ( uses_trait( $class, ServiceLocator::class ) ) {
                $definition->addMethodCall(
                    'setServiceLocator',
                    [$this->serviceLocator],
                );
                $add[] = 'ServiceLocator';
            }

            if ( uses_trait( $class, SettingsAccessor::class ) ) {
                $definition->addMethodCall(
                    'setSettingsProvider',
                    [$this->settingsProvider],
                );
                $add[] = 'SettingsAccessor';
            }

            if ( $add ) {
                $report->item( $class );

                foreach ( $add as $item ) {
                    $report->add( $item );
                }
                $report->separator();
            }
        }

        $report->output();
    }
}
