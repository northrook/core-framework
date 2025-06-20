<?php

declare(strict_types=1);

namespace Core\Framework\Event;

use Core\Framework\Lifecycle\LifecycleEvent;
use Core\Symfony\ToastService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Core\Symfony\ToastService\{ToastMessage, ToastView};
use Symfony\Component\HttpKernel\KernelEvents;
use Generator;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, TerminateEvent};

final class ToastMessageInjector extends LifecycleEvent implements EventSubscriberInterface
{
    /** @var ToastMessage[] */
    protected array $messages = [];

    public function __construct( protected ToastService $toast ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::RESPONSE  => ['parseSessionBag', -64],
            KernelEvents::TERMINATE => ['renderToastMessages', 24],
        ];
    }

    public function parseSessionBag( ResponseEvent $event ) : void
    {
        if ( $this->skipEvent() || ! $this->toast->hasMessages() ) {
            return;
        }

        $this->profilerStart( 'response.toasts' );

        $this->messages = $this->toast->getAllMessages();

        $this->profilerStop( 'response.toasts' );
    }

    public function renderToastMessages( TerminateEvent $event ) : void
    {
        $this->profilerStart( 'response.toasts' );

        foreach ( $this->printMessages() as $message ) {
            $this->profilerLap( 'response.toasts' );
            echo $message;
        }

        $this->profilerStop( 'response.toasts' );
    }

    /**
     * @return Generator<ToastView>
     */
    private function printMessages() : Generator
    {
        foreach ( $this->messages as $message ) {
            yield $message->getView();
        }
    }
}
