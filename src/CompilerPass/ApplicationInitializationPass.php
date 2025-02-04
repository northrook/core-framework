<?php

declare(strict_types=1);

namespace Core\CompilerPass;

use Core\Symfony\Console\Output;
use Core\Symfony\DependencyInjection\CompilerPass;
use Support\{Filesystem, Normalize, Time};
use JetBrains\PhpStorm\Language;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use SplFileInfo;
use Exception, LogicException, InvalidArgumentException;

final class ApplicationInitializationPass extends CompilerPass
{
    protected readonly string $defaultsDirectory;

    public function __construct( protected ?bool $override = null )
    {
        $this->defaultsDirectory = Normalize::path( \dirname( __DIR__, 2 ).'/config.app' );
    }

    public function compile( ContainerBuilder $container ) : void
    {
        $this->override ??= (bool) $this->parameterBag->get( 'kernel.debug' );

        $this->normalizePathParameters();

        $this->initializeDefaultConfiguration();
    }

    protected function initializeDefaultConfiguration() : void
    {
        $app_defaults = new Finder();

        $app_defaults->files()->in( $this->defaultsDirectory )->name( ['*.php', '*.yaml'] );

        foreach ( $app_defaults as $default ) {
            $project_path = $this->getProjectPath( $default );

            if ( ! ( $this->override ?? $this->overrideExistingFile( $project_path ) ) ) {
                $this->console->info( 'Skipping existing file: '.$project_path );

                continue;
            }

            $config = $this->createPhpConfig( $default->getRealPath() );

            Filesystem::save( $project_path, $config );
        }
    }

    protected function createPhpConfig( string $source, bool $canEdit = true ) : string
    {
        $contents = \file_get_contents( $source );

        if ( ! $contents ) {
            throw new LogicException( 'Could not read file: '.$source );
        }

        $config = \trim( \substr( $contents, \strlen( '<?php' ) ) );

        $config = (string) \preg_replace( '#^\h*?/\*\*.*?@noinspection.*?\*/\s\R*#ms', '', $config );

        $dateTime = Time::now();

        $name            = \pathinfo( $source, PATHINFO_BASENAME );
        $timestamp       = $dateTime->unixTimestamp;
        $date            = $dateTime->format( 'Y-m-d H:i:s e' );
        $generator       = __CLASS__;
        $contentDataHash = $this::hashConfiguration( $contents );

        $comment = $canEdit ? <<<'EOL'
            You may edit this file as you please.
                       
               This file will be re-generated by the Container Builder
               if this comment or the file itself is removed.
            EOL
                : 'Do not edit it manually.';

        $config = <<<PHP
            <?php
            
            /*--------------------------------------------------------{$timestamp}-
            
               Name      : {$name}
               Generated : {$date}
            
               This file is generated by:
               {$generator}.
            
               {$comment}
            
            -#{$contentDataHash}#------------------------------------------------*/
            
            {$config}
            PHP;

        $config = \rtrim( $config ).PHP_EOL;

        $config = (string) \preg_replace( '#^\h+$#m', '', $config );

        return \str_replace( PHP_EOL, "\n", $config );
    }

    /**
     * Takes in a PHP `$string`.
     *
     * Trims:
     * - opening `<?php`
     * - all comments
     * - whitespace
     *
     * Returns a `xxh3` hash of the `$string`.
     *
     * @param string $string
     *
     * @return string
     */
    public static function hashConfiguration(
        #[Language( 'PHP' )]
        string $string,
    ) : string {
        if ( \str_starts_with( $string, '<?php' ) ) {
            $string = \trim( \substr( $string, \strlen( '<?php' ) ) );
        }

        $removeComments = [
            '#^\h*?/\*\*.*?\*/\s\R*#ms', // PHP block comments
            '#\h*?//.+\R*#m',            // Single line comments
            '#\h*?/\*.*?\*/\R*#ms',      // Block comments
            "#\s+#",                     // Whitespace
        ];

        $string = (string) \preg_replace( $removeComments, ' ', $string );

        $string = \trim( $string );

        return \hash( 'xxh3', $string );
    }

    private function normalizePathParameters() : void
    {
        foreach ( $this->parameterBag->all() as $key => $value ) {
            // Only parse prefixed keys
            if ( \str_starts_with( $key, 'dir.' ) || \str_starts_with( $key, 'path.' ) ) {
                // Skip pure-placeholders
                if ( \str_starts_with( $value, '%' ) && \str_ends_with( $value, '%' ) ) {
                    continue;
                }

                // Normalize and report
                try {
                    $value = Normalize::path( $value );
                    $this->parameterBag->set( $key, $value );
                }
                catch ( Exception $e ) {
                    $message = Output::format( Output::MARKER, 'error' )."{$key} : {$e->getMessage()}";
                    Output::printLine( $message );
                }
            }
        }
    }

    private function getProjectPath( string|SplFileInfo $path ) : string
    {
        if ( \is_string( $path ) ) {
            $path = new SplFileInfo( $path );
        }

        $relativePath = \substr( $path->getRealPath(), \strlen( $this->defaultsDirectory ) );

        return Normalize::path( [$this->projectDirectory, $relativePath] );
    }

    private function overrideExistingFile( string $path ) : bool
    {
        // Always create if no file is found
        if ( ! Filesystem::exists( $path ) ) {
            return true;
        }

        // Check readability
        if ( ! \is_readable( $path ) ) {
            // Attempt to set permissions on fail
            $setPermissions = \chmod( $path, 0755 );
            if ( ! $setPermissions ) {
                $this->console->error( 'Skipping existing file due to read permissions error: '.$path );
                return false;
            }
        }

        // Always skip existing .yaml configs
        if ( \pathinfo( $path, PATHINFO_EXTENSION ) === 'yaml' ) {
            $this->console->warning( 'Passed [yaml] file: '.$path );
            return false;
        }

        $stream = \fopen( $path, 'r' );

        if ( false === $stream ) {
            $message = __CLASS__.' is unable to open file : '.$path;
            throw new InvalidArgumentException( $message );
        }

        $lineCount            = 0;
        $overrideExistingFile = true;

        while ( false !== ( $line = \fgets( $stream ) ) ) {
            $lineCount++;

            if ( \str_starts_with( $line, '/*---' ) ) {
                // If no timestamp is found, override
                $overrideExistingFile = ! \trim( $line, " \n\r\t\v\0/*-" );

                break;
            }

            if ( $lineCount >= 15 || \ctype_alpha( $line[0] ) ) {
                break;
            }
        }

        \fclose( $stream );

        return $overrideExistingFile;
    }
}
