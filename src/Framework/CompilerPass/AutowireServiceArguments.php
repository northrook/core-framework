<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Symfony\Component\DependencyInjection\{ContainerBuilder, ContainerInterface, Definition, Reference};
use Core\Interface\ActionInterface;
use Core\Profiler\Interface\Profilable;
use JetBrains\PhpStorm\Deprecated;
use Psr\Log\LoggerAwareInterface;
use Core\Autowire\{ServiceLocator, SettingsAccessor};
use Core\Framework\Controller;
use Core\Symfony\Console\{ListReport};
use Core\Symfony\DependencyInjection\{CompilerPass};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Support\{class_adopts_any, uses_trait};
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use ReflectionClass;
use Throwable;

/**
 * @internal
 */
final class AutowireServiceArguments extends CompilerPass
{
    public const string LOCATOR = 'core.service_locator';

    private readonly ListReport $report;

    protected readonly Definition $serviceLocator;

    protected readonly Definition $settingsProvider;

    public function compile( ContainerBuilder $container ) : void
    {
        $this->report = new ListReport( __METHOD__ );

        $this->serviceLocator   = $this->getDefinition( $this::LOCATOR );
        $this->settingsProvider = $this->getDefinition( service( 'core.settings_provider' ) );

        $this
            ->registerTaggedServices()
            ->autowireServices()
            ->autowireInterfaces();

        $this->report->output();
    }

    protected function autowireInterfaces() : self
    {
        if ( ! \interface_exists( ActionInterface::class ) ) {
            $this->report->error( "\Core\Interface\ActionInterface does not exist; ".__METHOD__.' skipped.' );
            return $this;
        }
        if ( ! \interface_exists( LoggerAwareInterface::class ) ) {
            $this->report->error( "\Psr\Log\LoggerAwareInterface does not exist; ".__METHOD__.' skipped.' );
            return $this;
        }
        if ( ! \interface_exists( Profilable::class ) ) {
            $this->report->error(
                "\Core\Profiler\Interface\Profilable does not exist; ".__METHOD__.' skipped.',
            );
            return $this;
        }

        foreach ( $this->container->getDefinitions() as $service => $definition ) {
            $class = $definition->getClass();

            if ( ! $class ) {
                continue;
            }

            $add = [];

            if ( \is_subclass_of( $class, ActionInterface::class ) ) {
                $definition->setAutowired( true );
                $definition->addTag( 'controller.service_arguments' );

                $add[] = 'Tag: controller.service_arguments';
            }

            if ( \is_subclass_of( $class, LoggerAwareInterface::class ) ) {
                try {
                    $reflect         = new ReflectionClass( $class );
                    $setLoggerMethod = $reflect->getMethod( 'setLogger' );
                    $docBlock        = $setLoggerMethod->getDocComment();
                }
                catch ( Throwable $e ) {
                    $this->report->error( $e->getMessage() );

                    continue;
                }

                $isDeprecated = ( $docBlock && \str_contains( $docBlock, '@deprecated' ) )
                                || $reflect->getAttributes( Deprecated::class );

                if ( $isDeprecated ) {
                    $this->report->warning( 'setLogger deprecated for: '.$class );

                    continue;
                }

                $loggerSet = false;

                foreach ( $definition->getMethodCalls() as $methodCall ) {
                    if ( $methodCall[0] === 'setLogger' ) {
                        $loggerSet = true;

                        break;
                    }
                }

                if ( $this->verbose && $loggerSet ) {
                    $this->report->warning( 'setLogger already set for: '.$service );

                    continue;
                }

                $logger = new Reference(
                    'logger',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                );

                $definition->addMethodCall( 'setLogger', [$logger] );

                $add[] = 'Call: setLogger '.$logger->__toString();
            }

            if ( \is_subclass_of( $class, Profilable::class ) ) {
                $stopwatch = new Reference(
                    'debug.stopwatch',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                );

                $definition->addMethodCall( 'setProfiler', [$stopwatch] );

                $add[] = 'Call: setProfiler '.$stopwatch->__toString();
            }

            if ( $add ) {
                $this->report->item( $class );

                foreach ( $add as $item ) {
                    $this->report->add( $item );
                }
                $this->report->separator();
            }
        }

        return $this;
    }

    private function autowireServices() : self
    {
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
                $this->report->item( $class );

                foreach ( $add as $item ) {
                    $this->report->add( $item );
                }
                $this->report->separator();
            }
        }

        return $this;
    }

    private function registerTaggedServices() : self
    {
        $arguments = $this->serviceLocator->getArguments()[0] ?? [];

        dump( [__METHOD__ => \get_defined_vars()] );

        foreach ( $this->taggedServiceIds( $this::LOCATOR, 'controller.service_arguments' ) as $id ) {
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
                $service_argument = $this::LOCATOR;
                $this->console->error(
                    $this::class." could not find a serviceId for '{$id}' when parsing services tagged with '{$service_argument}'.",
                );
            }
        }

        $this->serviceLocator->setArguments( [$arguments] );

        return $this;
    }
}
