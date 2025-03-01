<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\{BinaryFileResponse, Response};
use Core\Framework\Route;
use Core\Symfony\Toast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route(
    name    : Route::ASSETS,
    methods : 'GET',
    schemes : 'https',
)]
final class AssetController
{
    // Listen for all /assets/any .. report 404
    // report/log internally - periodically re-validate using CRON
    //
    #[Route( '/assets/{path}', 'fallback', requirements : ['path' => '.+'] )]
    public function index( ?string $path, Toast $toast ) : void
    {
        // dump( \get_defined_vars() );
        $toast->warning( 'Requested asset not found', $path );
        throw new NotFoundHttpException();

        // return new Response(
        //     'Requested asset not found: '.$path,
        //     404,
        // );
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon( Toast $toast ) : BinaryFileResponse
    {
        $toast->info( 'Serving favicon' );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
