<?php

declare(strict_types=1);

namespace Core\Framework\CompilerPass;

use Core\Container\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApplicationConfigPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        // TODO: Implement compile() method.
    }
}
