<?php

namespace Core\Framework\Autowire;

use Core\Framework;
use Core\Framework\DependencyInjection\ServiceContainer;

trait Settings
{
    use ServiceContainer;

    /**
     * @final
     *
     * @return Framework\Settings
     */
    final protected function settings() : Framework\Settings
    {
        return $this->serviceLocator( Framework\Settings::class );
    }
}
