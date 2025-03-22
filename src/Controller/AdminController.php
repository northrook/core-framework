<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Framework\Response\{Parameters, Template};
use Core\Framework\Controller\Attribute\{OnDocument};
use Core\View\{Document};
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;

// #[Route(
//     path    : '/',
//     name    : 'admin.',
//     host    : 'admin.{domain}.{tld}',
//     methods : 'GET',
//     schemes : 'https',
// )]
#[Template( document : 'admin.latte' )]
final class AdminController extends Controller
{
    #[OnDocument]
    public function onDocumentResponse(
        Document   $document,
        Parameters $parameters,
    ) : void {
        $parameters->set( 'hello', 'hello there' );
        $document
            ->title( 'Admin' )
            ->assets( 'style.core', 'script.core', 'script.htmx' );
    }

    #[Route(
        path : '/',
        name : 'dashboard',
    )]
    #[Template( 'admin/dashboard.latte' )]
    public function dashboard(
        Profiler $profiler,
    ) : void {
        // $profiler->disable();

        dump(
            $this,
            \get_defined_vars(),
        );
    }
}
