<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Response\Template;
use Core\Framework\Lifecycle\LifecycleEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Core\Framework\ResponseRenderer;
use ReflectionClass;
use ReflectionException;

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

        $this->controllerOnViewMethods( $event );

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

    private function controllerOnViewMethods( ResponseEvent $event ) : void
    {
        if ( ! $_controller_class = $event->getRequest()->attributes->get( '_controller_class' ) ) {
            return;
        }

        \assert( is_string( $_controller_class ) && class_exists( $_controller_class ) );

        try {
            ( new ReflectionClass( $_controller_class ) )
                ->getMethod( 'controllerResponseMethods' )
                ->invoke( $_controller_class );
        }
        catch ( ReflectionException $exception ) {
            $this->logger?->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
    }
}
