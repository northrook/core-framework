<?php

declare(strict_types=1);

namespace Core\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class AssetController
{
    // Listen for all /assets/any .. report 404
    // we could also handle this with an eventListener?
    //
    // #[Route( '/favicon.ico', 'core:favicon' )]
    public function index() : mixed
    {
        dump( __METHOD__ );
        throw new NotFoundHttpException();
    }
}
