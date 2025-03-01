<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Framework\Controller\ControllerEventSubscriber;
use Core\Symfony\ToastService;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\{FinishRequestEvent, TerminateEvent};

final class ToastMessageInjector extends ControllerEventSubscriber
{
    protected array $messages = [];

    public function __construct( protected ToastService $toast ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::FINISH_REQUEST => 'parseSessionBag',
            KernelEvents::TERMINATE      => ['renderToastMessages', 24],
        ];
    }

    public function parseSessionBag( FinishRequestEvent $event ) : void
    {
        if ( $this->skipEvent() || ! $this->toast->hasMessages() ) {
            return;
        }

        $this->messages = $this->toast->getAllMessages();
    }

    public function renderToastMessages( TerminateEvent $event ) : void
    {
        foreach ( $this->messages as $message ) {
            echo $message->getView();
        }
    }
}
