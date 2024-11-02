<?php

namespace Core\Framework\DependencyInjection;

use Core\Framework  ;

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
