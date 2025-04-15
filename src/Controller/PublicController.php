<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Response\{Parameters, Template};
use Core\Pathfinder;
use Core\Symfony\Toast;
use Core\Framework\Controller;
use Core\Framework\Controller\Attribute\{OnDocument};
use Core\View\{Document, ViewFactory};
use Symfony\Component\HttpFoundation\{Request};
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends Controller
{
    #[OnDocument]
    public function onDocumentResponse( Document $document ) : void
    {
        $document
            ->title( 'Public Document Title' )
            ->assets( 'style.core', 'script.core', 'script.htmx' );
    }

    #[Route(
        path     : '/',
        name     : 'index',
        defaults : ['route' => 'index'],
        priority : -1_024,
    ), ]
    #[Template( 'welcome.latte' )]
    public function index(
        ?string    $route,
        Document   $document,
        Request    $request,
        Pathfinder $pathfinder,
    ) : string {
        $path = $pathfinder(
            'dir.root',
        );
        $document(
            'Index Demo Template',
        );

        return <<<HTML
            <body>
                <h1>Hello there!</h1>
                {$path}
            </body>
            HTML;
    }

    #[Route( 'tailwind', 'tailwind_demo' )]
    #[Template( 'demo.latte' )]
    public function tailwind(
        Document $document,
    ) : string {
        dump( $this->request->attributes->get( '_route_params' ) );
        $document( 'Tailwind Demo Template' );
        // $document->script( 'https://cdn.tailwindcss.com', 'tailwindcss' );

        return 'tailwind.latte';
    }

    #[Route( 'demo', 'view_demo' )]
    #[Template( 'demo.latte' )]
    public function demo(
        Document    $document,
        Toast       $toast,
        Parameters  $parameters,
        ViewFactory $view,
    ) : string {
        // $assetManager->factory->locator()->scan();
        $document( 'Index Demo Template' );

        $toast->info( 'Hello there, this is a toast.' );

        // $toast(
        //     'info',
        //     'Useful information Toast.',
        //     'It has some details as well. How thoughtful.',
        // );

        // foreach ( \range( 0, \rand( 2, 7 ) ) as $key => $value ) {
        //     $status      = (string) $toast::STATUS[ \array_rand( $toast::STATUS ) ];
        //     $description = $key % 2 == 0 ? 'Description' : null;
        //
        //     $timeout = 3600 + ( $key * 1000 );
        //
        //     $toast(
        //             $status,
        //             'Hello there, this is a ' . $status . '. Seed: ' . $key,
        //             $description,
        //             $timeout,
        //     );
        // }

        return 'demo.latte';
    }

    #[Route( 'hello', 'html_boilerplate' )]
    public function boilerplate() : string
    {
        return <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sample Page</title>
            </head>
            <body>
                <h1>Hello there!</h1>
                <p>This is a simple HTML boilerplate with a heading and some content. Feel free to customize it as needed.</p>
                <p>HTML is a powerful language for structuring content on the web, and this basic template is a great starting point for building more complex pages.</p>
            </body>
            </html>
            HTML;
    }
}
