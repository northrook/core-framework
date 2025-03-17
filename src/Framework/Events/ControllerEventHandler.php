<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Symfony\Component\HttpKernel\Event\{ResponseEvent};
use Core\Framework\ResponseRenderer;

final class ControllerEventHandler extends ControllerAwareEvent
{
    public function __construct( protected readonly ResponseRenderer $responseRenderer ) {}

    public function __invoke( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        $this->profiler?->event( __METHOD__, 'View' );

        if ( $this->getSetting( 'view.template.clear_cache', false ) ) {
            $this->responseRenderer
                ->templateEngine
                ->clearTemplateCache();
        }

        $profileContent = $this->profiler?->event( 'Response Content', 'View' );
        $this->responseRenderer
            ->setResponseContent(
                $event,
                $this->template,
            );
        $profileContent?->stop();

        $profileRender = $this->profiler?->event( 'Render Response', 'View' );
        $event->setResponse(
            $this->responseRenderer->getResponse(),
        );
        $profileRender?->stop();

        $this->profiler?->stop( category : 'View' );
    }
}
