<?php

declare(strict_types=1);

namespace Core\Container;

use Core\Console\StatusReport;
use Core\Exception\CompilerException;
use Support\ClassFinder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition, Reference};
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Support\{class_basename, cli_format, is_path, normalize_path};

abstract class CompilerPass implements CompilerPassInterface
{
    protected readonly ContainerBuilder $container;

    protected readonly StatusReport $report;

    protected readonly ParameterBagInterface $parameterBag;

    protected readonly string $projectDirectory;

    protected bool $verbose = false;

    abstract protected function compile( ContainerBuilder $container ) : void;

    /**
     * Processes the service container to perform specific operations and initialize necessary services.
     *
     * @param ContainerBuilder $container the container builder instance to be processed
     *
     * @return void
     */
    final public function process( ContainerBuilder $container ) : void
    {
        $this->container        = $container;
        $this->report           = new StatusReport( $this::class );
        $this->parameterBag     = $container->getParameterBag();
        $this->projectDirectory = $this->resolveProjectDirectory();
        $this->verbose          = (bool) $this->parameterBag->get( 'kernel.debug' );

        // echo cli_format(
        //     ' '.\trim( 'Compiler' ).' ',
        //     'magenta',
        //     'bg_black',
        //     'bold',
        // ).' '.class_basename( $this::class )."\n";

        $this->compile( $container );

        $this->report->output();
    }

    /**
     * Retrieves the service definition by its identifier.
     *
     * If the definition is not found and the $newOnMissing parameter is set to a value other than false,
     * a new Definition instance is created and returned. If $newOnMissing is true, the new Definition will
     * use the provided identifier; otherwise, $newOnMissing is treated as the new Definition's identifier.
     *
     * If the definition is not found and the $nullable parameter is true, null is returned.
     *
     * @param Reference|ReferenceConfigurator|string $id           the identifier of the service definition
     * @param bool|string                            $newOnMissing optionally specify whether a new definition should be
     *                                                             created on a missing service
     * @param bool                                   $nullable     whether to return null when the definition is not found
     *
     * @return ($nullable is true ? null|Definition : Definition)
     *
     * @throws ServiceNotFoundException if the definition cannot be found, and $nullable is false
     */
    final protected function getDefinition(
        string|ReferenceConfigurator|Reference $id,
        bool|string                            $newOnMissing = false,
        bool                                   $nullable = false,
    ) : ?Definition {
        $id = \is_string( $id ) ? $id : $id->__toString();

        $hasDefinition = $this->container->hasDefinition( $id );

        if ( $hasDefinition ) {
            return $this->container->getDefinition( $id );
        }

        if ( $newOnMissing !== false ) {
            return new Definition( $newOnMissing === true ? $id : $newOnMissing );
        }

        if ( $nullable ) {
            return null;
        }

        throw new ServiceNotFoundException(
            id  : $id,
            msg : $this::class." cannot find required '{$id}' definition.",
        );
    }

    /**
     * Retrieves a parameter's value as a valid normalized path.
     *
     * The method fetches the specified parameter from the parameter bag and validates if it represents
     * a valid path. If an optional $append string is provided, it will be appended to the resolved path.
     * The resulting path is then normalized before being returned.
     *
     * @param string      $key    the key of the desired parameter to retrieve
     * @param null|string $append an optional string to append to the resolved path
     *
     * @return string the normalized path
     *
     * @throws CompilerException if the parameter value is not a valid path
     */
    final protected function getParameterPath( string $key, ?string $append = null ) : string
    {
        $path = $this->parameterBag->get( $key );

        if ( \is_string( $path ) && is_path( $path ) ) {
            if ( $append ) {
                $path .= "/{$append}";
            }

            return normalize_path( $path );
        }

        throw new CompilerException(
            message : \sprintf(
                "The '%s' parameter must be a valid path, but returned '%s' from the ParameterBag.",
                $key,
                \var_export( $path, true ),
            ),
            label   : __METHOD__,
        );
    }

    /**
     * Retrieves a list of service IDs that are tagged with the specified tags.
     *
     * @param string ...$tag One or more tag names to filter services by
     *
     * @return string[]
     */
    final protected function taggedServiceIds( string ...$tag ) : array
    {
        $serviceIds = [];

        foreach ( $tag as $name ) {
            $serviceIds = [
                ...$serviceIds,
                ...$this->container->findTaggedServiceIds( $name ),
            ];
        }

        return \array_keys( $serviceIds );
    }

    /**
     * Retrieves a list of declared classes based on specified filtering parameters.
     *
     * @param null|string       $inDirectory   [optional] directory to scan for classes
     * @param null|class-string $subclassOf    [optional] filter results to subclasses of this class
     * @param bool              $hasDefinition [false] only classes that have service container definitions
     *
     * @return class-string[]
     */
    final protected function getDeclaredClasses(
        ?string $inDirectory = null,
        ?string $subclassOf = null,
        bool    $hasDefinition = false,
    ) : array {
        /**
         * @var class-string[] $discoveredClasses
         */
        $discoveredClasses = \array_filter(
            $inDirectory ? ClassFinder::scan( $inDirectory )->getArray()
                        : [...\get_declared_classes(), ...$this->container->getServiceIds()],
            static fn( $class ) => \class_exists( (string) $class ),
        );
        $declaredClasses = [];

        foreach ( $discoveredClasses as $class ) {
            $class = (string) $class;
            if ( $subclassOf && ! \is_subclass_of( $class, $subclassOf ) ) {
                continue;
            }

            if ( $hasDefinition && ! $this->container->hasDefinition( $class ) ) {
                continue;
            }

            $declaredClasses[$class] = true;
        }

        return \array_keys( $declaredClasses );
    }

    /**
     * Resolves and validates the project directory path.
     *
     * @return string normalized project directory path
     *
     * @throws CompilerException if the `kernel.project_dir` parameter is not a valid writable directory
     */
    private function resolveProjectDirectory() : string
    {
        $projectDirectory = $this->parameterBag->get( 'kernel.project_dir' );

        if (
            \is_string( $projectDirectory )
            && \is_dir( $projectDirectory )
            && \is_writable( $projectDirectory )
        ) {
            return normalize_path( $projectDirectory );
        }

        throw new CompilerException(
            message : \sprintf(
                "The '%s' parameter '%s' must be a valid writable directory path.",
                'kernel.project_dir',
                \var_export( $projectDirectory, true ),
            ),
            label   : __METHOD__,
        );
    }
}
