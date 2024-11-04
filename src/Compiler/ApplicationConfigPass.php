<?php

namespace Core\Framework\Compiler;

use Core\Framework\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApplicationConfigPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        dump( __METHOD__.' container compiled check: '.( $container->isCompiled() ? 'true' : 'false' ) );

        $this->path( 'config/packages/debug.yaml' )->remove();

        $this
            ->generateAppKernel()
            ->generatePublicIndex()
            ->generateControllerRouteConfig()
            ->createConfigServices()
            ->configurePreload()
            ->coreControllerRoutes();
    }

    protected function generateAppKernel( bool $override = false ) : self
    {
        $this->createPhpFile(
            'src/Kernel.php',
            <<<PHP
                <?php
                
                declare(strict_types=1);
                
                namespace App;
                
                use Symfony\Bundle\FrameworkBundle\Kernel as FrameworkKernel;
                use Symfony\Component\HttpKernel\Kernel as HttpKernel;
                
                final class Kernel extends HttpKernel
                {
                    use FrameworkKernel\MicroKernelTrait;
                }
                PHP,
            $override,
        );

        return $this;
    }

    protected function generatePublicIndex( bool $override = false ) : self
    {
        $this->createPhpFile(
            'public/index.php',
            <<<PHP
                <?php
                
                declare(strict_types=1);
                
                require_once \dirname(__DIR__).'/vendor/autoload_runtime.php';
                
                return function (array \$context): App\Kernel {
                    return new App\Kernel(\$context['APP_ENV'], (bool) \$context['APP_DEBUG']);
                };

                PHP,
            $override,
        );

        return $this;
    }

    protected function createConfigServices( bool $override = false ) : self
    {
        $this->path( 'config/services.yaml' )->remove();

        $this->createPhpFile(
            'config/services.php',
            <<<PHP
                <?php
                
                declare(strict_types=1);
                
                use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
                
                return static function( ContainerConfigurator \$container ) : void {
                
                    \$services = \$container->services();
                
                    // Defaults for App services.
                    \$services
                        ->defaults()
                        ->autowire()
                        ->autoconfigure();
                
                    \$services
                        // Make classes in src/ available to be used as services.
                        ->load( "App\\\\", __DIR__ . '/../src/' )
                        // We do not want to autowire DI, ORM, or Kernel classes.
                        ->exclude(
                            [
                                __DIR__ . '/../src/DependencyInjection/',
                                __DIR__ . '/../src/Entity/',
                                __DIR__ . '/../src/Kernel.php',
                            ],
                        );
                };

                PHP,
            $override,
        );
        return $this;
    }

    protected function configurePreload( bool $override = false ) : self
    {
        $this->createPhpFile(
            'config/preload.php',
            <<<'PHP'
                <?php
                
                declare(strict_types=1);
                
                if (\file_exists(\dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
                    \opcache_compile_file(\dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php');
                }

                PHP,
            $override,
        );

        return $this;
    }

    protected function generateControllerRouteConfig( bool $override = false ) : self
    {
        $this->path( 'config/routes.yaml' )->remove();

        $this->createPhpFile(
            'config/routes.php',
            <<<PHP
                <?php
                
                declare(strict_types=1);
                
                use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
                
                return static function( RoutingConfigurator \$routes ) : void {
                    \$routes->import(
                        [
                            'path'      => '../src/Controller/',
                            'namespace' => 'App\Controller',
                        ],
                        'attribute',
                    );
                };

                PHP,
            $override,
        );

        return $this;
    }

    protected function coreControllerRoutes() : self
    {
        // TODO : Ensure we set the controller attribute namespaces correctly

        $routes = [
            'core.controller' => [
                'resource' => [
                    'path'      => '@CoreBundle/src/Controller',
                    'namespace' => 'Core\Controller',
                ],
                'type' => 'attribute',
                // 'prefix'   => '/',
            ],
        ] ;

        $this->createYamlFile( 'config/routes/core.yaml', $routes, true );

        return $this;
    }
}
