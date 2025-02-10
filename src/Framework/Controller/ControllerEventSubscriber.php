<?php

declare(strict_types=1);

namespace Core\Framework\Controller;

use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\Template;
use Core\Exception\NotSupportedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;
use LogicException, BadMethodCallException;
use ReflectionAttribute, ReflectionClass;

abstract class ControllerEventSubscriber implements EventSubscriberInterface
{
    private bool $skipEvent;

    protected readonly LoggerInterface $logger;

    protected readonly Controller $controller;

    protected readonly ?Template $template;

    /**
     * @return bool
     */
    final protected function skipEvent() : bool
    {
        if ( isset( $this->skipEvent ) ) {
            return $this->skipEvent;
        }

        $this->logger->error(
            '{method} is only available after the {even} event.',
            [
                'method'    => __METHOD__,
                'even'      => 'kernel.controller',
                'exception' => new BadMethodCallException(),
            ],
        );

        // Always skip early calls
        return true;
    }

    /**
     * Configured by {@see RegisterEventSubscribers::controllerEventSubscriber}.
     *
     * @param ControllerEvent $event
     *
     * @return void
     */
    final public function validateRequestController( ControllerEvent $event ) : void
    {
        if ( isset( $this->skipEvent ) ) {
            if ( $event->getRequestType() === HttpKernelInterface::SUB_REQUEST ) {
                return;
            }

            throw new LogicException( __METHOD__.' was already called.' );
            // return;
        }

        // Only parse GET requests
        if ( $event->getRequest()->isMethod( 'GET' ) === false ) {
            $this->skipEvent = true;
            return;
        }

        // Set Request attributes
        $htmx = $event->getRequest()->headers->has( 'hx-request' );
        $event->getRequest()->attributes->set( 'hx-request', $htmx );
        $event->getRequest()->attributes->set( 'http-type', $htmx ? 'XMLHttpRequest' : 'HttpRequest' );
        $event->getRequest()->attributes->set( 'view-type', $htmx ? 'content' : 'document' );

        if ( $event->getController() instanceof ErrorController ) {
            $this->skipEvent = true;
            return;
        }

        if ( \is_array( $event->getController() ) ) {
            /** @noinspection PhpParamsInspection - ignore false-negative */
            $object = \current( $event->getController() );

            if ( ! $object instanceof Controller ) {
                $this->skipEvent = true;
                return;
            }

            $this->skipEvent  = false;
            $this->controller = $object;
        }
        else {
            dd(
                $event,
                new NotSupportedException(
                    '[TODO] Non-array callables.',
                ),
            );
        }

        $this->template = $this->resolveTemplateAttribute( $event );
    }

    protected function resolveTemplateAttribute( ControllerEvent $event ) : ?Template
    {
        foreach ( $event->getControllerReflector()->getAttributes(
            Template::class,
            ReflectionAttribute::IS_INSTANCEOF,
        ) as $attribute ) {
            return $attribute->newInstance();
        }

        foreach ( ( new ReflectionClass( $this->controller ) )->getAttributes(
            Template::class,
            ReflectionAttribute::IS_INSTANCEOF,
        ) as $attribute ) {
            return $attribute->newInstance();
        }

        return null;
    }
}
