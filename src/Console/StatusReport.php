<?php

declare(strict_types=1);

namespace Core\Console;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\{Stopwatch, StopwatchEvent};

final class StatusReport
{
    /** @var string[] */
    private array $items = [];

    private readonly SymfonyStyle $console;

    private readonly StopwatchEvent $stopwatch;

    public readonly string $title;

    public function __construct(
        string                  $title,
        private readonly string $marker = '│',
        private readonly string $note = '┊',
        private readonly string $add = '+',
        private readonly string $remove = '-',
        private readonly string $warning = '⋄',
        private readonly string $error = '!',
    ) {
        if ( \str_contains( $title, '::' ) ) {
            $title = \trim( \strrchr( $title, '\\' ) ?: $title, '\\' );
        }
        $this->title     = $title;
        $this->stopwatch = ( new Stopwatch() )->start(
            name     : $this->title,
            category : 'cli_list_report',
        );
        $this->console = new SymfonyStyle( new StringInput( '' ), new ConsoleOutput() );
    }

    public function __destruct()
    {
        $this->stopwatch->ensureStopped();
    }

    private function addItem( string $message, string $type = 'marker' ) : void
    {
        $this->stopwatch->lap();
        $type = match ( $type ) {
            'note'    => $this->format( $this->note, 'fg=gray;options=bold' ),
            'skip'    => $this->format( $this->note, 'fg=green' ),
            'warning' => $this->format( $this->warning, 'fg=yellow;options=bold' ),
            'error'   => $this->format( $this->error, 'error' ),
            'add'     => $this->format( $this->add, 'info' ),
            'remove'  => $this->format( $this->remove, 'error' ),
            default   => $this->format( $this->marker, 'fg=bright-green' ),
        };
        $this->items[] = $type.$message;
    }

    public function line( string $message, int $indnet = 3 ) : void
    {
        if ( $indnet ) {
            $message = \str_repeat( ' ', $indnet ).$message;
        }

        $this->items[] = $message;
    }

    public function item( string $message ) : void
    {
        $this->addItem( $message );
    }

    public function skip( string $message ) : void
    {
        $this->addItem( $message, 'skip' );
    }

    public function note( string $message ) : void
    {
        $this->addItem( $message, 'note' );
    }

    public function warning( string $message ) : void
    {
        $this->addItem( $message, 'warning' );
    }

    public function error( string $message ) : void
    {
        $this->addItem( $message, 'error' );
    }

    public function add( string $message ) : void
    {
        $this->addItem( $message, 'add' );
    }

    public function remove( string $message ) : void
    {
        $this->addItem( $message, 'remove' );
    }

    public function separator() : void
    {
        $this->items[] = '';
    }

    public function output() : void
    {
        if ( empty( $this->items ) ) {
            $this->stopwatch->stop();
            return;
        }

        $this->console->newLine();

        $time = $this->stopwatch->stop()->getDuration();

        $style_time = 'fg=gray;options=bold';
        $style_fade = 'fg=gray;';

        $message = " <{$style_time}>{$time}</{$style_time}><{$style_fade}>ms</{$style_fade}>";
        $message .= $this->format( $this->title, 'fg=bright-white;options=bold' );

        $this->console->writeln( $message );

        foreach ( $this->items as $item ) {
            $this->console->writeln( $item );
        }

        $this->console->newLine();
    }

    /**
     * @param string|string[] $message
     * @param string          $style
     *
     * @return string
     */
    private function format(
        string|array $message,
        string       $style,
    ) : string {
        return ( new FormatterHelper() )->formatBlock( $message, $style );
    }
}
