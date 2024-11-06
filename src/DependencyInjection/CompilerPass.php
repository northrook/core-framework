<?php

namespace Core\Framework\DependencyInjection;

use Core\Framework\Pathfinder;
use JetBrains\PhpStorm\Language;
use Northrook\Filesystem\Path;
use Support\Time;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

abstract class CompilerPass implements CompilerPassInterface
{
    protected readonly string $projectDirectory;

    protected readonly ParameterBagInterface $parameterBag;

    abstract public function compile( ContainerBuilder $container ) : void;

    final public function process( ContainerBuilder $container ) : void
    {
        $this->parameterBag     = $container->getParameterBag();
        $this->projectDirectory = $this->setProjectDirectory();

        $this->compile( $container );
    }

    protected function path( string $fromProjectDir ) : Path
    {
        return new Path( "{$this->projectDirectory}/{$fromProjectDir}" );
    }

    /**
     * @param string                         $fromProjectDir
     * @param array<array-key, mixed>|string $data
     * @param bool                           $override
     *
     * @return void
     */
    final protected function createYamlFile(
        string       $fromProjectDir,
        #[Language( 'PHP' )] string|array $data,
        bool         $override = false,
    ) : void {
        $path = $this->path( $fromProjectDir );

        if ( $path->exists() && false === $override ) {
            return;
        }

        $path->save( Yaml::dump( $data ) );
    }

    final protected function createPhpFile(
        string    $fromProjectDir,
        #[Language( 'PHP' )] string    $php,
        bool      $override = false,
        string ...$comment,
    ) : void {
        $path = $this->path( $fromProjectDir );

        if ( $path->exists() && false === $override ) {
            return;
        }

        $path->save( $this->parsePhpString( $php, ...$comment ) );
    }

    private function setProjectDirectory() : string
    {
        $projectDirectory = $this->parameterBag->get( 'kernel.project_dir' );

        \assert(
            \is_string( $projectDirectory )
                && \is_dir( $projectDirectory )
                && \is_writable( $projectDirectory ),
        );

        return Pathfinder::normalize( $projectDirectory );
    }

    private function parsePhpString( #[Language( 'PHP' )] string $php, string ...$comment ) : string
    {
        if ( ! \str_starts_with( $php, '<?php' ) ) {
            throw new UnexpectedValueException( __METHOD__.': The provided PHP string has no opening tag.' );
        }

        if ( \str_ends_with( $php, '?>' ) ) {
            throw new UnexpectedValueException( __METHOD__.': PHP strings must not end with a closing tag.' );
        }

        $generator = '    This file is autogenerated by '.$this::class.'.';
        $generated = '    Date: '.( new Time() )->datetime;

        $separator = \str_repeat( '-', \strlen( $generator ) );

        $header   = [];
        $header[] = "\n\n/*{$separator}\n";
        $header[] = $generator;
        $header[] = $generated;
        if ( $comment ) {
            $header[] = '';

            foreach ( $comment as $line ) {
                $header[] = '    '.$line;
            }
        }
        $header[] = "\n{$separator}*/\n\n";

        $content = \preg_replace(
            pattern     : '#<\?php\s+?(?=\S)#A',
            replacement : '<?php'.\implode( "\n", $header ),
            subject     : $php,
        );

        \assert( \is_string( $content ) );

        return $content;
    }
}
