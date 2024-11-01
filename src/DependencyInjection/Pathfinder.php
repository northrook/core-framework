<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

trait Pathfinder
{
    use ServiceContainer;

    /**
     * @param  ?string                              $get
     * @return null|\Core\Service\Pathfinder|string
     */
    final protected function pathfinder( ?string $get = null ) : \Core\Service\Pathfinder|null|string
    {
        if ( $get ) {
            return $this->serviceLocator( \Core\Service\Pathfinder::class )->get( $get );
        }
        return $this->serviceLocator( \Core\Service\Pathfinder::class );
    }
}
