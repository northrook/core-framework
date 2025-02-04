<?php

declare(strict_types=1);

namespace Core;

use Core\CompilerPass\ApplicationInitializationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Core Symfony Framework.
 *
 * @author Martin Nielsen
 */
final class CoreBundle extends AbstractBundle
{
    /**
     * @param array<array-key, mixed> $config
     * @param ContainerConfigurator   $container
     * @param ContainerBuilder        $builder
     *
     * @return void
     */
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        $container->import( __DIR__.'/../config/application.php' );
        $container->import( __DIR__.'/../config/parameters.php' );
        $container->import( __DIR__.'/../config/pathfinder.php' );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build( ContainerBuilder $container ) : void
    {
        $container->addCompilerPass( new ApplicationInitializationPass() );
    }
}
