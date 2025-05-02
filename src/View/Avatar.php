<?php

namespace Core\View;

use Core\Action\Users;
use Core\Symfony\DependencyInjection\ServiceContainer;
use Core\Symfony\Interface\ServiceContainerInterface;
use Core\View\ComponentFactory\ViewComponent;
use const Support\AUTO;

#[ViewComponent( 'ui:avatar' )]
final class Avatar extends Component implements ServiceContainerInterface
{
    use ServiceContainer;

    public function __construct( public readonly Users $user ) {}

    public function __invoke(
        ?string $username = AUTO,
    ) : self {
        return $this;
    }
}
