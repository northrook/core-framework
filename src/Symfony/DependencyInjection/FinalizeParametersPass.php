<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FinalizeParametersPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        dump( $this->parameterBag->all() );
    }
}
