<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContactController extends AbstractController
{

    public function __construct(View $view)
    {
        $this->view = $view;
    }
    public function index(ServerRequestInterface $request): ResponseInterface {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/contact.html.twig')
        );
    }
}