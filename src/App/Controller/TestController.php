<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends AbstractController
{

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function index(ServerRequestInterface $request, array $params): ResponseInterface {
        $id = $params['id'];
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('test.html.twig', ['id' => $id])
        );
    }
}