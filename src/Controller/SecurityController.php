<?php

namespace Core\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    name     : 'security:',
    priority : 1,
)]
final class SecurityController
{
    #[Route(
        path : '/login',
        name : 'login',
    )]
    public function login() : Response
    {
        return new Response( __METHOD__ );
    }
}
