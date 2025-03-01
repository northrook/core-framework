<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Symfony\ToastService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\{FinishRequestEvent, TerminateEvent};

final class ToastMessageInjector implements EventSubscriberInterface
{
    protected bool $hasMessages = false;

    public function __construct( protected ToastService $toast ) {}

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::FINISH_REQUEST => 'parseSessionBag',
            KernelEvents::TERMINATE      => ['renderToastMessages', 32],
            // KernelEvents::EXCEPTION => 'onKernelException',
            // KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function parseSessionBag( FinishRequestEvent $event ) : void
    {
        if ( $event->getRequest()->attributes->get( '_route' ) === '_wdt' ) {
            return;
        }

        dump( $this->toast->getAllMessages( true ) );
    }

    public function renderToastMessages( TerminateEvent $event ) : void
    {
        if ( ! $this->hasMessages ) {
            return;
        }

        dump( $this );
        // $toasts = [];
        //
        // foreach ( $this->toast->getAllMessages() as $message ) {
        //     $toasts[$message->id] ??= $message->getView();
        //     // $toasts[$message->id] ??= $this->componentFactory->render(
        //     //     'view.component.toast',
        //     //     $message->getArguments(),
        //     // );
        // }
        //
        // dump( $toasts );
        //
        // echo \implode( '', $toasts );
    }
}
