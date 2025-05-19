<?php

// -------------------------------------------------------------------
// config\framework\services
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Core\Action\Headers;
use Core\Framework\CompilerPass\AutowireServiceArguments;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

// use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
// use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
// use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

return static function(
    ContainerConfigurator $container,
) : void {
    /**
     * @used-by \Core\Autowire\ServiceLocator
     */
    $container->services()
        ->set( AutowireServiceArguments::LOCATOR )
        ->tag( 'container.service_locator' )
        ->args(
            [
                [
                    // Symfony
                    RequestStack::class          => service( 'request_stack' ),
                    ParameterBagInterface::class => service( 'parameter_bag' ),
                    RouterInterface::class       => service( 'router' ),
                    HttpKernelInterface::class   => service( 'http_kernel' ),
                    SerializerInterface::class   => service( 'serializer' ),
                    // Security
                    // TokenStorageInterface::class => service(
                    //     'security.token_storage',
                    // )->nullOnInvalid(),
                    // CsrfTokenManagerInterface::class => service(
                    //     'security.csrf.token_manager',
                    // )->nullOnInvalid(),
                    // AuthorizationCheckerInterface::class => service(
                    //     'security.authorization_checker',
                    // ),
                ],
            ],
        );
};
