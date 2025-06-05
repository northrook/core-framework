<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Symfony\Component\DependencyInjection\{ContainerBuilder, ContainerInterface, Definition, Reference};
use Core\Exception\CompilerException;
use Core\Container\CompilerPass;
use Core\Interface\ActionInterface;
use Core\Profiler\Interface\Profilable;
use JetBrains\PhpStorm\Deprecated;
use Psr\Log\LoggerAwareInterface;
use Core\Autowire\{Profiler, ServiceLocator, SettingsProvider};
use Core\Framework\Controller;
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

    // private readonly ListReport $report;

    protected readonly Definition $serviceLocator;

    protected readonly Definition $settingsProvider;

    public function compile( ContainerBuilder $container ) : void
    {
        $this->serviceLocator   = $this->getDefinition( $this::LOCATOR );
        $this->settingsProvider = $this->getDefinition( service( 'core.settings_provider' ) );

        $this
            ->registerTaggedServices()
            ->autowireServices()
            ->autowireInterfaces();
    }

    protected function autowireInterfaces() : self
    {
        if ( ! \interface_exists( ActionInterface::class ) ) {
            CompilerException::error(
                message  : "\Core\Interface\ActionInterface does not exist; ".__METHOD__.' skipped.',
                continue : $this->verbose,
            );
            return $this;
        }
        if ( ! \interface_exists( LoggerAwareInterface::class ) ) {
            CompilerException::error(
                message  : "\Psr\Log\LoggerAwareInterface does not exist; ".__METHOD__.' skipped.',
                continue : $this->verbose,
            );
            return $this;
        }
        if ( ! \trait_exists( Profiler::class ) ) {
            CompilerException::error(
                message  : "\Core\Autowire\Profiler does not exist; ".__METHOD__.' skipped.',
                continue : $this->verbose,
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
                    CompilerException::error( $e->getMessage() );

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

            if ( uses_trait( $class, SettingsProvider::class ) ) {
                $definition->addMethodCall(
                    'setSettingsProvider',
                    [$this->settingsProvider],
                );
                $add[] = 'SettingsProvider';
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

        // dump( [__METHOD__ => \get_defined_vars()] );

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
                $this->report->error(
                    $this::class." could not find a serviceId for '{$id}' when parsing services tagged with '{$service_argument}'.",
                );
            }
        }

        $this->serviceLocator->setArguments( [$arguments] );

        return $this;
    }
}
