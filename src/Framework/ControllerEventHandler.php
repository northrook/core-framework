<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\Controller\ControllerEventSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, ViewEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use ReflectionClass;
use ReflectionException;
use Stringable;

final class ControllerEventHandler extends ControllerEventSubscriber
{
    public function __construct(
        protected readonly ResponseView    $responseView,
        protected readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::VIEW     => 'onKernelView',
            KernelEvents::RESPONSE => ['onKernelResponse', 32],
            // KernelEvents::EXCEPTION => 'onKernelException',
            // KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    /**
     * Generate and set an appropriate {@see Response}.
     *
     * @param ViewEvent $event
     *
     * @return void
     */
    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }

        /**
         * Call methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
         */
        try {
            ( new ReflectionClass( $this->controller ) )
                ->getMethod( 'controllerResponseMethods' )
                ->invoke( $this->controller );
        }
        catch ( ReflectionException $exception ) {
            $this->logger->error( $exception->getMessage(), ['exception' => $exception] );
        }

        $content = $event->getControllerResult();

        if ( \is_string( $content ) || $content instanceof Stringable ) {
            $content = (string) $content;
        }

        if ( ! ( \is_string( $content ) || \is_null( $content ) ) ) {
            $this->logger->error(
                message : 'Controller {controller} return value is {type}; {required}, {provided} provided as fallback.',
                context : [
                    'controller' => $this->controller,
                    'type'       => \gettype( $content ),
                    'required'   => 'string|null',
                    'provided'   => 'null',
                ],
            );
            $content = null;
        }

        // @phpstan-ignore-next-line
        \assert( \is_string( $content ) || \is_null( $content ) );

        $event->setResponse( new Response( $content ) );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() ) {
            return;
        }
        dump( $event, $this );
    }
}
