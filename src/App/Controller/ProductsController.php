<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Repository\Product\ProductRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductsController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(View $view, AuthenticationInterface $authentication, ProductRepository $productRepository){
        $this->view = $view;
        $this->authentication = $authentication;
        $this->productRepository = $productRepository;
    }

    public function index(ServerRequestInterface $request): ResponseInterface {
        $user = $this->authentication->user();
        $products = $this->productRepository->findAllMobileBikes();
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/products.html.twig', [
                'user' => $user,
                'products' => $products
            ])
        );
    }
}