<?php

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\{OnDocument, Template};
use Core\Symfony\DependencyInjection\SettingsAccessor;
use Core\View\{Document, Parameters};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    name     : 'security.',
    methods  : ['GET', 'POST'],
    schemes  : 'https',
    priority : 64,
)]
#[Template( 'security.latte' )]
final class SecurityController extends Controller
{
    use SettingsAccessor;

    #[OnDocument]
    public function onDocument( Document $document ) : void
    {
        $document->assets( 'style.core', 'script.core', 'script.htmx' );
    }

    #[Route( path : '/login/{$token}', name : 'login', ), ]
    #[Template( 'security/login.latte' )]
    public function login(
        Document   $document,
        Parameters $parameters,
        ?string    $token = null,
    ) : void {
        $document( 'Login' );
        dump( \get_defined_vars() );
    }

    /**
     * @param Document    $document
     * @param Parameters  $parameters
     * @param null|string $step
     *
     * @return void
     */
    #[Route(
        path : '/login/onboarding/{token}',
        name : 'onboarding',
    )]
    #[Template( 'security/onboarding.latte' )]
    public function onboarding(
        Document   $document,
        Parameters $parameters,
        ?string    $step = null,
    ) : void {
        // ! if auth.onboarding.disabled return [404]
        // On first visit, $step is null, showing welcome screen
        // .. a hx-request is sent with $step=sessionID - if valid start onboarding
        // .. each $step will indicate state

        dump( \get_defined_vars() );
        if ( ! $this->settings( 'auth.onboarding' ) ) {
            // Do this earlier, but only allow onboarding when enabled
            // First boot / no users registered defaults to auth.onboarding = true
            throw $this->notFoundException();
        }
        $document( 'Login' );
    }

    /**
     * @param Document   $document
     * @param Parameters $parameters
     * @param string     $token      [required] - generated magic token
     *
     * @return void
     */
    #[Route(
        path : '/login/verify/{token}',
        name : 'verify',
    )]
    public function verify(
        Document   $document,
        Parameters $parameters,
        string     $token,
    ) : void {
        $document( 'Email Verification' );
        dump( \get_defined_vars() );
    }

    #[Route(
        path : '/auth/recover/{token}',
        name : 'recovery',
    )]
    public function recovery(
        Document   $document,
        Parameters $parameters,
        ?string    $token = null,
    ) : void {
        $document( 'Account Recovery' );
        dump( \get_defined_vars() );
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
