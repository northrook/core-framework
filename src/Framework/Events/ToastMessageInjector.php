<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerAwareEvent;
use Core\Symfony\ToastService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Core\Symfony\ToastService\{ToastMessage, ToastView};
use Symfony\Component\HttpKernel\KernelEvents;
use Generator;
use Symfony\Component\HttpKernel\Event\{ResponseEvent, TerminateEvent};

final class ToastMessageInjector extends ControllerAwareEvent implements EventSubscriberInterface
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
        dump( __METHOD__.' '.( $this->skip() ? 'true' : 'false' ) );

        $this->profiler?->event( __METHOD__ );
        $this->messages = $this->toast->getAllMessages();
        $this->profiler?->stop( __METHOD__ );
    }

    public function renderToastMessages( TerminateEvent $event ) : void
    {
        foreach ( $this->printMessages() as $message ) {
            echo $message;
        }
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
