<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Controller\Attribute\Template;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Core\Framework\ResponseRenderer;

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
    protected const string CATEGORY = 'Response';

    public function __construct( protected readonly ResponseRenderer $responseRenderer ) {}

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

        $profileContent = $this->profiler?->event( 'Response Content' );
        $this->responseRenderer
            ->setResponseContent( $event, $template );
        $profileContent?->stop();

        $profileRender = $this->profiler?->event( 'Render Response' );
        $event->setResponse(
            $this->responseRenderer->getResponse(),
        );
        $profileRender?->stop();

        $this->profiler?->stop( $this::class );
    }
}
