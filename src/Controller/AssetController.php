<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\{BinaryFileResponse, Request};
use Core\Asset\ImageAsset;
use Core\AssetManager;
use Core\Framework\Route;
use Core\Symfony\Toast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
    public function index(
        Request      $request,
        Toast        $toast,
        AssetManager $assetManager,
    ) : void {
        $path = $request->getRequestUri();

        try {
            $image = $assetManager->getAsset( $path );

            \assert( $image instanceof ImageAsset );

            $toast->notice( 'Generated asset:', $image->name );
        }
        catch ( Throwable $exception ) {
            throw new NotFoundHttpException(
                $exception->getMessage(),
                $exception,
            );
        }
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon( Toast $toast ) : BinaryFileResponse
    {
        $toast->info( 'Serving favicon' );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
