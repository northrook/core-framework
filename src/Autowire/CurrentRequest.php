<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\HttpFoundation\Request;

trait CurrentRequest
{
    use ServiceContainer;

    final protected function getRequest() : Request
    {
        return $this->serviceLocator( Request::class );
    }
}
