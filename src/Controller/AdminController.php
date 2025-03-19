<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Framework\Response\Template;
use Core\Framework\Controller\Attribute\{OnDocument};
use Core\View\{Document};
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path     : '/admin/',
    name     : 'admin.',
    priority : 32,
)]
#[Template( document : 'admin.latte' )]
final class AdminController extends Controller
{
    #[OnDocument]
    public function onDocumentResponse( Document $document ) : void
    {
        $document
            ->title( 'Admin' )
            ->assets( 'style.core', 'script.core', 'script.htmx' );
    }

    #[Route(
        path : [
            'index'  => 'dashboard',
            'action' => 'dashboard/{action}',
        ],
        name : 'admin.dashboard',
    )]
    #[Template( 'admin/dashboard.latte' )]
    public function dashboard() : void {}
}
