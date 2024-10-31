<?php

namespace Core\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\Attribute\Required;

interface ServiceContainerInterface
{
    #[Required]
    public function setServiceLocator( ServiceLocator $serviceLocator ) : void;
}
