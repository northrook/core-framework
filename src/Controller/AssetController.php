<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\{BinaryFileResponse, Response};
use Core\Symfony\Toast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    name    : 'assets.',
    methods : ['GET'],
    schemes : 'https',
)]
final class AssetController
{
    // Listen for all /assets/any .. report 404
    // we could also handle this with an eventListener?
    //
    #[Route( '/assets/{path}', 'fallback', requirements : ['path' => '.+'] )]
    public function index( ?string $path, Toast $toast ) : Response
    {
        // dump( \get_defined_vars() );
        // throw new NotFoundHttpException();

        $toast->warning( 'Requested asset not found', $path );

        return new Response(
            'Requested asset not found: '.$path,
            404,
        );
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon() : BinaryFileResponse
    {
        dump( __METHOD__ );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
