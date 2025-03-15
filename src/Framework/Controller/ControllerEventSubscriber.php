<?php

declare(strict_types=1);

namespace Core\Framework\Controller;

use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\Template;
use Core\Profiler\Interface\SettableProfilerInterface;
use Core\Profiler\SettableStopwatchProfiler;
use Core\Symfony\DependencyInjection\SettingsAccessor;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use LogicException, BadMethodCallException;
use ReflectionAttribute, ReflectionClass;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class ControllerEventSubscriber implements
    EventSubscriberInterface,
    SettableProfilerInterface,
    LoggerAwareInterface
{
    use SettingsAccessor,
        SettableStopwatchProfiler,
        LoggerAwareTrait;

    private bool $skipEvent;

    protected readonly Controller $controller;

    protected readonly ?Template $template;

    final public function setProfiler( ?Stopwatch $stopwatch, ?string $category = null ) : void
    {
        $this->assignProfiler( $stopwatch, 'Controller' );
    }

    /**
     * @return bool
     */
    final protected function skipEvent() : bool
    {
        if ( isset( $this->skipEvent ) ) {
            return $this->skipEvent;
        }

        $this->logger?->error(
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

        $this->profiler?->event( __METHOD__ );

        $this->skipEvent = $this->resolveControllerEvent( $event );

        $this->template = $this->skipEvent
                ? null
                : $this->resolveTemplateAttribute( $event );

        $this->profiler?->stop( __METHOD__ );
    }

    private function resolveControllerEvent( ControllerEvent $event ) : bool
    {
        // Only parse GET requests
        if ( $event->getRequest()->isMethod( 'GET' ) === false ) {
            return true;
        }

        if ( $event->getController() instanceof ErrorController ) {
            return true;
        }

        if ( \is_array( $event->getController() ) ) {
            /** @noinspection PhpParamsInspection - ignore false-negative */
            $object = \current( $event->getController() );

            if ( $object instanceof Controller ) {
                $this->controller = $object;
                return false;
            }
        }

        $this->logger?->alert( __METHOD__.' did not find a controller.', ['event' => $event] );

        return true;
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
