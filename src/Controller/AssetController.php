<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\{BinaryFileResponse, RedirectResponse, Request};
use Core\Asset\ImageAsset;
use Core\{AssetManager, Framework\Exception\HttpNotFoundException, Pathfinder};
use Core\Symfony\Toast;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class AssetController
{
    #[Route( '/assets/{path}', 'fallback', requirements : ['path' => '.+'] )]
    public function index(
        Request      $request,
        Toast        $toast,
        AssetManager $assetManager,
        Pathfinder   $pathfinder,
    ) : RedirectResponse {
        $path = $request->getRequestUri();

        try {
            $image = $assetManager->getAsset( $path );
            \assert( $image instanceof ImageAsset );
        }
        catch ( Throwable $exception ) {
            throw new HttpNotFoundException(
                message     : "Unable to locate or generate asset: '{$path}'.",
                description : $exception->getMessage(),
                previous    : $exception,
            );
        }

        $toast->notice( 'Generated asset:', $image->name );

        $filePath = $image->generateImage( $path );

        if ( ! \file_exists( $filePath ) ) {
            throw new HttpNotFoundException(
                'Unable to locate or generate asset: '.$path,
                "The file '{$filePath}' does not exist.",
            );
        }

        // TODO : Possibly return 201 Created
        // Note that redirects help in-place hot-reloads of <img src..>
        return new RedirectResponse( $path );
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon( Toast $toast ) : BinaryFileResponse
    {
        $toast->info( 'Serving favicon' );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
