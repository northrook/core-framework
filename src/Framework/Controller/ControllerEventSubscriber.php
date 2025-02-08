<?php

namespace Core\Framework\Controller;

use Core\Framework\Controller;
use Core\Exception\{NotSupportedException};
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ControllerEvent};
use LogicException;

abstract class ControllerEventSubscriber implements EventSubscriberInterface
{
    private bool $skipEvent;

    protected readonly LoggerInterface $logger;

    protected readonly Controller $controller;

    final protected function skipEvent() : bool
    {
        if ( isset( $this->skipEvent ) ) {
            return $this->skipEvent;
        }

        $this->logger->error(
            '{method} is only available after the {even} event.',
            ['method' => __METHOD__, 'even' => 'kernel.controller'],
        );

        return false;
    }

    #[AsEventListener( 'kernel.controller' )]
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
