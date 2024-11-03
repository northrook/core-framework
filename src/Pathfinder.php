<?php

declare(strict_types=1);

namespace Core\Framework;

use Northrook\ArrayStore;
use Northrook\Exception\E_Value;
use Northrook\Logger\Log;
use Northrook\Resource\Path;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Support\getProjectRootDirectory;
use Throwable;

/**
 * @template TKey of array-key
 * @template-covariant TValue of ?string
 */
final readonly class Pathfinder
{
    /** @var ArrayStore<TKey,TValue> */
    private ArrayStore $pathfinderCache;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private string                $pathfinderCachePath,
    ) {}

    public function get( string $path ) : ?string
    {
        if ( $this->isLocalPath( $path ) ) {
            return $path;
        }
        $path = $this->pathfinder()->get( $this->cacheKey( $path ) ) ?? $this->resolvePath( $path );
        \assert( \is_string( $path ) || \is_null( $path ) );
        return $path;
    }

    public function getPath( string $path ) : ?Path
    {
        $resolved = $this->get( $path );
        return $resolved ? new Path( $resolved ) : null;
    }

    public function has( string $path ) : bool
    {
        return $this->pathfinder()->has( $this->cacheKey( $path ) );
    }

    /**
     * @param string $path
     *
     * @return TValue
     */
    private function resolvePath( string $path ) : ?string
    {
        // we already know the cache does not contain the requested $path

        // Normalize separators to a forward slash
        $path = \str_replace( '\\', '/', $path );

        // Determine what, if any, separator is used
        $separator = \str_contains( $path, '/' ) ;

        // If the requested $path has no separator, should be a key
        if ( false === $separator ) {
            $value = $this->getParameter( name : $path );

            if ( ! $value ) {
                Log::warning( 'No value for {path}.', ['path' => $path] );
                return null;
            }

            Log::info( 'Resolved {value} from  {path}.', ['value' => $value, 'path' => $path] );
            return $value;
        }

        // Split the $path by the first $separator
        [$root, $tail] = \explode( '/', $path, 2 );

        // Resolve the $root key
        $parameter = $this->getParameter( $root );

        if ( ! $parameter ) {
            return null;
        }

        $resolved = new Path( [$parameter, $tail] );

        if ( $resolved->exists ) {
            $this->pathfinder()->set( $this->cacheKey( $path ), $resolved->path );
        }

        return $resolved->path;
    }

    /**
     * @param string $name {@see ParameterBagInterface::get}
     *
     * @return ?string
     */
    private function getParameter( string $name ) : ?string
    {
        try {
            $parameter = $this->parameterBag->get( $name );
        }
        catch ( Throwable|ParameterNotFoundException $exception ) {
            E_Value::error(
                '{pathfinder} requested the non-existent paraneter {parameter}.',
                [
                    'pathfinder' => $this::class,
                    'parameter'  => $name,
                ],
                $exception,
                false,
            );
            return null;
        }

        \assert( \is_string( $parameter ) );

        return $parameter;
    }

    /**
     * Retrieve the Pathfinder {@see ArrayStore} cache.
     *
     * @return ArrayStore<TKey,TValue>
     */
    private function pathfinder() : ArrayStore
    {
        return $this->pathfinderCache ??= new ArrayStore(
            $this->pathfinderCachePath,
            $this::class,
        );
    }

    /**
     * # Normalise a `string` or `string[]`, assuming it is a `path`.
     *
     * - If an array of strings is passed, they will be joined using the directory separator.
     * - Normalises slashes to system separator.
     * - Removes repeated separators.
     * - Will throw a {@see ValueError} if the resulting string exceeds {@see PHP_MAXPATHLEN}.
     *
     * ```
     * normalizePath( './assets\\\/scripts///example.js' );
     * // => '.\assets\scripts\example.js'
     * ```
     *
     * @param string ...$path
     */
    public static function normalize( string ...$path ) : string
    {

        // Normalize separators
        $nroamlized = \str_replace( ['\\', '/'], DIRECTORY_SEPARATOR, $path );

        $isRelative = DIRECTORY_SEPARATOR === $nroamlized[0];

        // Implode->Explode for separator deduplication
        $exploded = \explode( DIRECTORY_SEPARATOR, \implode( DIRECTORY_SEPARATOR, $nroamlized ) );

        // Ensure each part does not start or end with illegal characters
        $exploded = \array_map( static fn( $item ) => \trim( $item, " \n\r\t\v\0\\/" ), $exploded );

        // Filter the exploded path, and implode using the directory separator
        $path = \implode( DIRECTORY_SEPARATOR, \array_filter( $exploded ) );

        if ( ( $length = \mb_strlen( $path ) ) > ( $limit = PHP_MAXPATHLEN - 2 ) ) {
            E_Value::error(
                '{method} resulted in a string of {length} characters, exceeding the {limit} character limit.'
                           .PHP_EOL.'Operation was halted to prevent buffer overflow.',
                [
                    'method' => __METHOD__,
                    'length' => (string) $length,
                    'limit'  => (string) $limit,
                ],
                throw: true,
            );
        }

        // Preserve intended relative paths
        if ( $isRelative ) {
            $path = DIRECTORY_SEPARATOR.$path;
        }

        return $path;
    }

    // :: Internal

    private function cacheKey( string $string ) : string
    {
        $string = \str_replace( '\\', '/', $string );

        if ( ! \str_contains( $string, '/' ) ) {
            return $string;
        }

        [$root, $tail] = \explode( '/', $string, 2 );
        if ( $tail ) {
            $tail = '/'.\str_replace( '.', ':', $tail );
        }
        return $root.$tail;
    }

    private function getProjectRootDirectory() : string
    {
        try {
            $path = $this->parameterBag->get( 'kernel.project_dir' );
        }
        catch ( ParameterNotFoundException $exception ) {
            Log::exception( $exception );
            $path = getProjectRootDirectory();
        }
        \assert( \is_string( $path ) && \is_dir( $path ) );
        return $path;
    }

    private function isLocalPath( string $path ) : false|string
    {
        static $projectRootDirectory;
        $projectRootDirectory ??= $this::normalize( $this->getProjectRootDirectory() );

        $normalizedPath = $this::normalize( $path );

        if ( ! \str_starts_with( $normalizedPath, $projectRootDirectory ) ) {
            return false;
        }

        return $normalizedPath;
    }
}
