<?php

declare(strict_types=1);

namespace Core;

use Core\AssetManager\Compiler\RegisterAssetServices;
use Core\View\Compiler\RegisterViewComponentsPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Core\Framework\CompilerPass\{ApplicationInitialization,
    RegisterCoreServices,
    RegisterEventSubscribers
};
use Core\Symfony\Compiler\{AutodiscoverServicesPass, AutowireInterfaceDependencies};
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
        $container->import( __DIR__.'/../config/response.php' );
        $container->import( __DIR__.'/../config/view.php' );
        $container->import( __DIR__.'/../config/framework/assets.php' );
        $container->import( __DIR__.'/../config/framework/controllers.php' );
        $container->import( __DIR__.'/../config/framework/profiler.php' );
        $container->import( __DIR__.'/../config/framework/services.php' );
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
            ->addCompilerPass( new RegisterCoreServices() )
            ->addCompilerPass( new RegisterAssetServices() )
            ->addCompilerPass( new RegisterViewComponentsPass() )
            ->addCompilerPass( new ApplicationInitialization() )
            ->addCompilerPass( new RegisterEventSubscribers() )
            ->addCompilerPass( new AutowireInterfaceDependencies(), priority : -256 );
    }
}
