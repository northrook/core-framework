<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    #[Route( '/assets/{path}', 'core:favicon' )]
    public function index( ?string $path ) : mixed
    {
        dump( \get_defined_vars() );
        throw new NotFoundHttpException();
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon() : BinaryFileResponse
    {
        dump( __METHOD__ );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
