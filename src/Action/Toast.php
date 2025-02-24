<?php

declare(strict_types=1);

namespace Core\Action;

use Core\Interface\ActionInterface;
use Core\Symfony\DependencyInjection\Autodiscover;
use Core\Framework\Service\ToastService;

#[Autodiscover]
final class Toast implements ActionInterface
{
    public const string
        INFO    = 'info',
        NOTICE  = 'notice',
        SUCCESS = 'success',
        WARNING = 'warning',
        ERROR   = 'danger';

    public function __construct( private readonly ToastService $toast ) {}

    public function getService() : ToastService
    {
        return $this->toast;
    }

    /**
     * @param 'danger'|'info'|'notice'|'success'|'warning' $status
     * @param string                                       $message
     * @param null|string|string[]                         $description [optional] accepts {@see Tag::INLINE}
     * @param ?int                                         $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string                                      $icon        [auto] based on `$status`
     *
     * @return void
     */
    public function __invoke(
        string            $status,
        string            $message,
        null|string|array $description = null,
        ?int              $timeout = null,
        ?string           $icon = null,
    ) : void {
        $this->getService()->addMessage( $status, $message, $description, $timeout, $icon );
    }
}
