<?php

namespace Core\Framework;

use Core\Exception\{NotSupportedException};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ControllerEvent};
use BadMethodCallException;
use LogicException;

abstract class ControllerEventSubscriber implements EventSubscriberInterface
{
    // Skip by default
    private bool $skipEvent;

    protected readonly Controller $controller;

    final protected function skipEvent() : bool
    {
        return $this->skipEvent ?? throw new BadMethodCallException(
            __METHOD__." is only available after the 'kernel.controller' event.",
        );
    }

    final public function validateRequestController( ControllerEvent $event ) : void
    {
        if ( isset( $this->skipEvent ) ) {
            throw new LogicException( __METHOD__.' was already called.' );
            // return;
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
            throw new NotSupportedException(
                '[TOOD] Non-array callables.',
            );
        }
    }
}
