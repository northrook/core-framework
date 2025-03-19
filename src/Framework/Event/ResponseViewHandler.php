<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Core\Framework\Lifecycle\LifecycleEvent;
use Core\Framework\ResponseRenderer;
use Core\Framework\Response\Template;

/**
 * {@see ResponseEvent}
 *
 * Uses the {@see ResponseRenderer} to generate the content `HTML`.
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @final  ✅
 */
final class ResponseViewHandler extends LifecycleEvent
{
    public function __construct(
        protected readonly ResponseRenderer $responseRenderer,
    ) {}

    public function __invoke( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->profiler?->event( $this::class );

        if ( $this->getSetting( 'view.template.clear_cache', false ) ) {
            $this->responseRenderer
                ->templateEngine
                ->clearTemplateCache();
        }

        $template = $event->getRequest()->attributes->get( '_template' );

        \assert( \is_null( $template ) || $template instanceof Template );

        $profileContent = $this->profiler?->event( 'response.content' );
        $this->responseRenderer
            ->setResponseContent( $event, $template );
        $profileContent?->stop();

        $profileRender = $this->profiler?->event( 'render.response' );
        $event->setResponse(
            $this->responseRenderer->getResponse(),
        );
        $profileRender?->stop();

        $this->profiler?->stop( $this::class );
    }
}
