<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Controller\AbstractController;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController extends AbstractController
{
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function index(ServerRequestInterface $request): ResponseInterface {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/dashboard.html.twig')
        );
    }
}