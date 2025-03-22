<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpFoundation\{BinaryFileResponse, Request};
use Core\Asset\ImageAsset;
use Core\{AssetManager, Framework\Exception\HttpNotFoundException, Pathfinder};
use Core\Symfony\Toast;
use Support\Image;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

// #[Route(
//     name    : 'assets.',
//     methods : 'GET',
//     schemes : 'https',
// )]
final class AssetController
{
    #[Route( '{path}', 'fallback', requirements : ['path' => '.+'] )]
    public function index(
        Request      $request,
        Toast        $toast,
        AssetManager $assetManager,
        Pathfinder   $pathfinder,
    ) : BinaryFileResponse {
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

        return new BinaryFileResponse(
            file               : $filePath,
            status             : 200,
            headers            : [
                'Content-Type' => Image::mimeType( $filePath ),
            ],
            contentDisposition : 'inline',
        );
    }

    #[Route( '/favicon.ico', 'favicon' )]
    public function favicon( Toast $toast ) : BinaryFileResponse
    {
        $toast->info( 'Serving favicon' );
        return new BinaryFileResponse( __DIR__.'/../../public/favicon.ico' );
    }
}
