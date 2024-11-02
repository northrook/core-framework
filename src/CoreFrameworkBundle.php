<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\DependencyInjection\RegisterCoreServicesPass;
use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Core Symfony Framework.
 *
 * @author Martin Nielsen
 */
final class CoreFrameworkBundle extends AbstractBundle
{

    #[Override]
    public function getPath() : string
    {
        return \dirname( __DIR__ );
    }

    #[Override]
    public function build( ContainerBuilder $container ) : void
    {
        parent::build( $container );

        // Generate application config files and update kernel and public index files
        $container->addCompilerPass( new RegisterCoreServicesPass() );
    }

    /**
     * @param array<array-key, mixed> $config
     * @param ContainerConfigurator   $container
     * @param ContainerBuilder        $builder
     *
     * @return void
     */
    #[Override]
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {

        \array_map( [$container, 'import'], $this->config() );
    }

    /**
     * @return array<int, string>
     */
    private function config() : array
    {
        return \glob( \dirname( __DIR__, 2 ).'/config/framework/*.php' ) ?: [];
    }
}
