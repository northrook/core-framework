<?php

namespace Core\Action;

use Core\Interface\ActionInterface;
use Core\Symfony\DependencyInjection\Autodiscover;
use const Support\AUTO;

#[Autodiscover( autowire : true )]
final class Users implements ActionInterface
{
    private ?string $userId = null;

    public function __invoke( ?string $user = AUTO ) : self
    {
        $this->userId = $user;
        return $this;
    }

    public function getUsername( ?string $user = AUTO ) : string
    {
        $user ??= $this->userId;
        return $user ?? 'Anonymous';
    }

    public function getEmail( ?string $user = AUTO ) : string
    {
        $user ??= $this->userId;
        return $user ?? 'anony.mous@example.com';
    }
}
