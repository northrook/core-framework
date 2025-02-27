<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller;
use Symfony\Component\Routing\Attribute\Route;

final class AssetController extends Controller
{
    // Listen for all /assets/any .. report 404
    // #[Route( '/favicon.ico', 'core:favicon' )]
    public function index() : mixed
    {
        dump( __METHOD__ );
        throw $this->notFoundException();
    }
}
