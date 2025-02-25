<?php

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\Template;
use Core\Symfony\DependencyInjection\SettingsAccessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    name     : 'security.',
    methods  : ['GET', 'POST'],
    schemes  : 'https',
    priority : 64,
)]
final class SecurityController extends Controller
{
    use SettingsAccessor;

    #[Route( path : '/login', name : 'login', ), ]
    #[Template( 'security/login.latte' )]
    public function login(
        ?string $_path,
        ?string $token = null,
    ) : Response {
        dump( [__METHOD__, ...\get_defined_vars()] );
        return new Response( __METHOD__."->{$_path}" );
    }

    #[Route(
        path : '/login/onboarding/{token}',
        name : 'onboarding',
    )]
    public function onboarding(
        ?string $_path,
        ?string $token = null,
    ) : Response {
        dump( [__METHOD__, ...\get_defined_vars()] );
        if ( ! $this->settings( 'auth.onboarding' ) ) {
            // Do this earlier, but only allow onboarding when enabled
            // First boot / no users registered defaults to auth.onboarding = true
            throw $this->notFoundException();
        }

        return new Response( __METHOD__."->{$_path}" );
    }

    #[Route(
        path : '/login/verify/{token}',
        name : 'verify',
    )]
    public function verify(
        ?string $_path,
        ?string $token = null,
    ) : Response {
        dump( [__METHOD__, ...\get_defined_vars()] );

        return new Response( __METHOD__."->{$_path}" );
    }

    #[Route(
        path : '/auth/recover/{token}',
        name : 'recovery',
    )]
    public function recovery(
        ?string $_path,
        ?string $token = null,
    ) : Response {
        dump( [__METHOD__, ...\get_defined_vars()] );

        return new Response( __METHOD__."->{$_path}" );
    }

    #[Route(
        path    : [
            'recover' => '/password-recovery',
            'verify'  => '/_auth/verify/{token}',
        ],
        name    : 'user',
        methods : ['GET', 'POST'],
        schemes : 'https',
    )]
    public function user( string $_route ) : Response
    {
        return new Response( __METHOD__."->{$_route}" );
    }
}
