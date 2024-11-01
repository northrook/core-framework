<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ServiceContainer
{
    protected readonly ServiceLocator $serviceLocator;

    #[Required]
    final public function setServiceLocator( ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @final
     *
     * @template Service
     *
     * @param class-string<Service> $get
     *
     * @return Service
     */
    final protected function serviceLocator( string $get ) : mixed
    {
        return $this->serviceLocator->get( $get );
    }
}
