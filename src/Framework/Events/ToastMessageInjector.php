<?php

declare(strict_types=1);

namespace Core\Framework\Events;

use Core\Symfony\ToastService;
use Symfony\Component\HttpKernel\Event\{TerminateEvent};

final class ToastMessageInjector
{
    public function __construct( private ToastService $toast ) {}

    public function __invoke( TerminateEvent $event ) : void
    {
        if ( ! $this->toast->hasMessages() ) {
            return;
        }

        $toasts = [];

        foreach ( $this->toast->getAllMessages() as $message ) {
            $toasts[$message->id] ??= $message->getView();
            // $toasts[$message->id] ??= $this->componentFactory->render(
            //     'view.component.toast',
            //     $message->getArguments(),
            // );
        }

        dump( $toasts );

        echo \implode( '', $toasts );
    }
}
