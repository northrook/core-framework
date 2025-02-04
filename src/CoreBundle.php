<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\CompilerPass\ApplicationInitializationPass;
use Core\Symfony\Compiler\AutodiscoverServicesPass;
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
        $container->import( __DIR__.'/../config/framework/controllers.php' );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build( ContainerBuilder $container ) : void
    {
        $container
            ->addCompilerPass( new AutodiscoverServicesPass(), priority : 1_024 )
            ->addCompilerPass( new ApplicationInitializationPass() );
    }
}
