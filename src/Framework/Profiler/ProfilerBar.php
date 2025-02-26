<?php

namespace Core\Framework\Profiler;

use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class ProfilerBar
{
    protected readonly TerminateEvent $event;

    protected string $inlineStyles;

    protected string $token;

    public function __construct( private readonly ?KernelInterface $kernel = null ) {}

    public function __invoke( TerminateEvent $event ) : void
    {
        if ( $event->getRequest()->attributes->get( '_route' ) === '_wdt' ) {
            return;
        }

        $this->token = $event->getResponse()->headers->get( 'x-debug-token' ) ?: '';

        if ( ! $this->token ) {
            return;
        }

        $this->inlineStyles = <<<'CSS'
            profiler {
              --color: currentColor;
              --background: rgb(from currentColor calc(255 - r) calc(255 - g) calc(255 - b));
              --outline: rgb(from currentColor calc(222 - r) calc(222 - g) calc(222 - b));
              --status: currentColor;
              position: fixed;
              bottom: 3px;
              right: 3px;
              display: flex;
              flex-direction: row;
              gap: .25ch;
              color: var(--color);
              font-size: 12px;
              font-family: monospace;
              border-radius: 3px;
              backdrop-filter: blur(2px);
              box-shadow: 0 1px 6px 0 var(--outline);
              overflow: clip;
              /*outline: 1px solid deeppink;*/
            }
            profiler * {
              color: var(--color);
            }
            profiler a {
              font-family: monospace;
              padding: 3px 5px;
              opacity: .75;
            }
            profiler a > prefix {
              --color: var(--status);
              --background: hsla( from currentColor h calc(s - 25) calc(l + 25)  / 0.15 );
              margin-inline-end: 1ch;
              background: var(--background);
              box-shadow: -.5ch 0 0 1ch var(--background);
            }
            profiler a > route {
              opacity: .75;
            } 
            profiler a > path {
              margin-inline-end: .5ch;
            }
            
            profiler::before,
            profiler a::before {
              content: '';
              position: absolute;
              inset: 0;
              z-index: -1;
            }
            profiler::before {
              background: var(--background);
              border-radius: inherit;
              opacity: .75;
            }
            profiler a {
              position: relative;
            }
            profiler a::before {
              background: var(--outline);
              z-index: -1;
              opacity: 0;
              border-radius: inherit;
            }
            profiler a:hover::before, profiler a:focus-within::before  {
              opacity: .5;
            }
            profiler:hover a, profiler:focus-within a {
              opacity:1;
            }
            profiler, profiler a,  profiler::before, profiler a::before {
              transition: opacity 200ms ease-in-out;
            }
            CSS;

        $this->event ??= $event;

        // if ( $event->getRequest()->getBasePath() )

        // . Timer - Use @Clerk to track - set a microtime on new Kernel,
        // . denote as done when onKernelController or onKernelRequest is called (match Symfony timer)

        // . Route = Controller, Route Name, HTTP status, current URL

        // . Logs - show if [warning] or aove

        // . Overview - CPU, memory, included files/classes, OPCache status, User IP, Server IP
        // . php version, server type/version

        $x_debug_token = $event->getResponse()->headers->get( 'x-debug-token' );

        // dd(
        //         $this->getElapsedTime(),
        //         $event->getResponse(),
        // );

        $html = <<<HTML
            <profiler debug-token="{$x_debug_token}">
            <style>{$this->inlineStyles}</style>
            {$this->getRequestInfo()}
            {$this->getElapsedTime()}
            </profiler>
            HTML;
        echo \Support\str_squish( $html );
        // dump( $this->stopwatch );

        // Idea is to inject a simple Profiler, and link to the Symfony Profiler
        // using the x-debug-token from Response Headers.

        // dump( get_defined_vars() );
    }

    private function getRequestInfo() : string
    {
        $path   = $this->event->getRequest()->getPathInfo();
        $route  = $this->event->getRequest()->attributes->get( '_route' );
        $code   = $this->event->getResponse()->getStatusCode();
        $status = match ( $code ) {
            200     => 'style="--status: #348636;"',
            404     => 'style="--status: #f5cd47;"',
            default => 'style="--status: #b60c3a"',
        };

        [$prefix, $route] = \explode( '.', $route, 2 );

        $requestInfo = <<<HTML
            <prefix>{$prefix}</prefix><route>{$route}</route>
            HTML;

        // dd( get_defined_vars(), $this->event->getRequest() );
        return <<<HTML
            <a href="/_profiler/{$this->token}?panel=request" target="_blank" {$status}>
            {$requestInfo}
            <path>{$path}</path>
            </a>
            HTML;
    }

    private function getElapsedTime() : string
    {
        $started    = $this->kernel->getStartTime() * 1_000;
        $terminated = \microtime( true )            * 1_000;
        $duration   = $terminated - $started;

        $time = \number_format( $duration, 2, '.', '' );

        $style = [];

        if ( $time > 100 ) {
            $style[] = 'color: orangered';
        }

        return <<<HTML
            <a
            href="/_profiler/{$this->token}?panel=time"
            style="{$this->css( $style )}"
            target="_blank"
            >
                {$time}<span style="color: initial; opacity: .75; margin-inline-start: .5ch">ms</span>
            </a>
            HTML;
    }

    /**
     * @param array<string, string> $style
     *
     * @return string
     */
    private function css( array $style ) : string
    {
        return \implode( '; ', $style );
    }
}
