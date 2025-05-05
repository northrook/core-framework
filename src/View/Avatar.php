<?php

namespace Core\View;

use Core\Action\Users;
use Core\View\ComponentFactory\ViewComponent;
use const Support\AUTO;

#[ViewComponent( 'ui:avatar' )]
final class Avatar extends Component
{
    public function __construct( public readonly Users $user ) {}

    public function __invoke(
        ?string $username = AUTO,
    ) : self {
        return $this;
    }
}
