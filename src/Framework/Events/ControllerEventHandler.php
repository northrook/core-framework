<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Symfony\Component\HttpKernel\Event\{ResponseEvent};
use Core\Framework\ResponseRenderer;

final class ControllerEventHandler extends ControllerAwareEvent
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

        $profileContent = $this->profiler?->event( 'Response Content' );
        $this->responseRenderer
            ->setResponseContent(
                $event,
                $this->template,
            );
        $profileContent?->stop();

        $profileRender = $this->profiler?->event( 'Render Response' );
        $event->setResponse(
            $this->responseRenderer->getResponse(),
        );
        $profileRender?->stop();

        $this->profiler?->stop( $this::class );
    }
}
