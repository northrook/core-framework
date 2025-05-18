<?php

declare(strict_types=1);

namespace Core;

use Core\AssetManager\Config\{AssetManifestPass, RegisterAssetsPass};
use Core\AssetManager\AssetManifest;
use Core\Symfony\DependencyInjection\FinalizeParametersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\{param, service};
use Core\Symfony\Compiler\{
    AutodiscoverServicesPass,
    AutowireInterfaceDependencies
};
use Core\Framework\CompilerPass\{
    ApplicationInitialization,
    RegisterServiceArguments,
};
use Core\View\Compiler\RegisterViewComponentsPass;

/**
 * Core Symfony Framework.
 *
 * @author Martin Nielsen
 */
final class CoreBundle extends AbstractBundle
{
    private const array CONFIG = [
        __DIR__.'/../config/application.php',
        __DIR__.'/../config/parameters.php',
        __DIR__.'/../config/pathfinder.php',
        __DIR__.'/../config/response.php',
        __DIR__.'/../config/template.php',
        __DIR__.'/../config/view.php',
        __DIR__.'/../config/framework/assets.php',
        __DIR__.'/../config/framework/cache.php',
        __DIR__.'/../config/framework/controllers.php',
        __DIR__.'/../config/framework/profiler.php',
        __DIR__.'/../config/framework/route_loaders.php',
        __DIR__.'/../config/framework/settings.php',
        __DIR__.'/../config/framework/services.php',
    ];

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
        \array_map( [$container, 'import'], $this::CONFIG );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build( ContainerBuilder $container ) : void
    {
        // Ensure required classes are available
        \class_exists( ContainerConfigurator::class );

        $container
            ->addCompilerPass( new AutodiscoverServicesPass(), priority : 1_024 )
            ->addCompilerPass( new RegisterAssetsPass() )
            ->addCompilerPass(
                new RegisterViewComponentsPass(
                    service( 'core.view.engine' ),
                    service( 'debug.stopwatch' ),
                    service( 'logger' ),
                    service( 'cache.component_pool' ),
                ),
            )
            ->addCompilerPass( new ApplicationInitialization() )
            ->addCompilerPass( new AutowireInterfaceDependencies(), priority : -256 )
            ->addCompilerPass( new RegisterServiceArguments(), priority : -264 )
            ->addCompilerPass(
                pass : new FinalizeParametersPass(),
                type : PassConfig::TYPE_OPTIMIZE,
            )
            ->addCompilerPass(
                pass : new AssetManifestPass(
                    service( AssetManifest::class ),
                ),
                type : PassConfig::TYPE_OPTIMIZE,
            );
    }
}
