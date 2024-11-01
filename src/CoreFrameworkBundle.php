<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Framework\DependencyInjection\RegisterCoreServicesPass;
use Core\Framework\Response\{Document, Headers, Parameters};
use Core\Service\{Pathfinder, Request};
use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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

        /** @used-by \Core\Framework\DependencyInjection\ServiceContainer */
        $container->services()
            ->set( 'core.service_locator' )
            ->tag( 'container.service_locator' )
            ->args(
                [[
                    Request::class    => service( Request::class ),
                    Pathfinder::class => service( Pathfinder::class ),
                    Document::class   => service( Document::class ),
                    Parameters::class => service( Parameters::class ),
                    Headers::class    => service( Headers::class ),

                    // Symfony
                    RouterInterface::class     => service( 'router' ),
                    HttpKernelInterface::class => service( 'http_kernel' ),
                    SerializerInterface::class => service( 'serializer' ),

                    // Security
                    TokenStorageInterface::class         => service( 'security.token_storage' )->nullOnInvalid(),
                    CsrfTokenManagerInterface::class     => service( 'security.csrf.token_manager' )->nullOnInvalid(),
                    AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),
                ]],
            );

        $container->services()->defaults()
            ->tag( 'controller.service_arguments' )
            ->autowire()

                // ResponseHeaderBag Service
            ->set( Headers::class )

                // Document Properties
            ->set( Document::class )

                // Template Parameters
            ->set( Parameters::class );
    }
}
