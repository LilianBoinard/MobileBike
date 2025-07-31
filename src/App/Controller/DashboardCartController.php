<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Controller\AbstractController;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\App\Repository\Product\ProductRepository;
use MobileBike\App\Repository\Order\OrderRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Exception\Exceptions\UnauthorizedException;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardCartController extends AbstractController
{
    private UserRepository $userRepository;
    private ProductRepository $productRepository;
    private OrderRepository $orderRepository;

    public function __construct(
        View $view,
        AuthenticationInterface $authentication,
        UserRepository $userRepository,
        ProductRepository $productRepository,
        OrderRepository $orderRepository
    ){
        $this->view = $view;
        $this->authentication = $authentication;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        // Récuperation des roles
        $isClient = $this->userRepository->isClient($user->id);
        $isAdmin = $this->userRepository->isAdministrator($user->id);

        // Initialisation du panier s'il n'existe pas
        $this->initializeCart();

        // Récupération des articles du panier
        $cartItems = $this->getCartItems();
        $cartTotal = $this->calculateCartTotal($cartItems);
        $cartCount = $this->getCartItemCount($cartItems);

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/cart.html.twig', [
                'user' => $user,
                'isClient' => $isClient,
                'isAdmin' => $isAdmin,
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal,
                'cartCount' => $cartCount
            ])
        );
    }

    /**
     * Initialise le panier dans la session s'il n'existe pas
     */
    public function initializeCart(): void
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Ajoute un produit au panier
     */
    public function addToCart(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        $data = $request->getParsedBody();
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Données invalides']));
        }

        // Vérification de l'existence du produit
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            return new Response(404, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Produit non trouvé']));
        }

        // Vérification du stock
        if ($product->stock_quantity < $quantity) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Stock insuffisant']));
        }

        $this->initializeCart();

        // Ajouter ou mettre à jour la quantité
        if (isset($_SESSION['cart'][$productId])) {
            $newQuantity = $_SESSION['cart'][$productId]['quantity'] + $quantity;
            if ($newQuantity > $product->stock_quantity) {
                return new Response(400, ['Content-Type' => 'application/json'],
                    json_encode(['success' => false, 'message' => 'Stock insuffisant']));
            }
            $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'added_at' => date('Y-m-d H:i:s')
            ];
        }

        return new Response(200, ['Content-Type' => 'application/json'],
            json_encode([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cartCount' => $this->getCartItemCount()
            ]));
    }

    /**
     * Met à jour la quantité d'un produit dans le panier
     */
    public function updateQuantity(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        $body = (string) $request->getBody();
        $data = json_decode($body, true);
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        if ($productId <= 0) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'ID produit invalide']));
        }

        $this->initializeCart();

        if (!isset($_SESSION['cart'][$productId])) {
            return new Response(404, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Produit non trouvé dans le panier']));
        }

        if ($quantity <= 0) {
            // Supprimer l'article si quantité = 0
            unset($_SESSION['cart'][$productId]);
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode([
                    'success' => true,
                    'message' => 'Produit retiré du panier',
                    'cartCount' => $this->getCartItemCount()
                ]));
        }

        // Vérification du stock
        $product = $this->productRepository->findById($productId);
        if ($product && $quantity > $product->stock_quantity) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Stock insuffisant']));
        }

        $_SESSION['cart'][$productId]['quantity'] = $quantity;

        return new Response(200, ['Content-Type' => 'application/json'],
            json_encode([
                'success' => true,
                'message' => 'Quantité mise à jour',
                'cartCount' => $this->getCartItemCount()
            ]));
    }

    /**
     * Supprime un produit du panier
     */
    public function removeFromCart(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }



        $body = (string) $request->getBody();
        $data = json_decode($body, true);
        $productId = (string) ($data['product_id'] ?? 0);

        if ($productId <= 0) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'ID produit invalide']));
        }

        $this->initializeCart();

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        return new Response(200, ['Content-Type' => 'application/json'],
            json_encode([
                'success' => true,
                'message' => 'Produit retiré du panier',
                'cartCount' => $this->getCartItemCount()
            ]));
    }

    /**
     * Vide complètement le panier
     */
    public function clearCart(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        $_SESSION['cart'] = [];

        return new Response(200, ['Content-Type' => 'application/json'],
            json_encode([
                'success' => true,
                'message' => 'Panier vidé',
                'cartCount' => 0
            ]));
    }

    /**
     * Procède au checkout et crée une commande
     */
    public function checkout(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        $this->initializeCart();

        if (empty($_SESSION['cart'])) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Panier vide']));
        }

        try {
            // Vérification des stocks avant création de la commande
            foreach ($_SESSION['cart'] as $productId => $cartItem) {
                $product = $this->productRepository->findById($productId);
                if (!$product || $product->stock_quantity < $cartItem['quantity']) {
                    return new Response(400, ['Content-Type' => 'application/json'],
                        json_encode([
                            'success' => false,
                            'message' => "Stock insuffisant pour le produit: {$product->name}"
                        ]));
                }
            }

            // Création de la commande
            $orderNumber = $this->generateOrderNumber();
            $orderId = $this->orderRepository->createOrder($user->id, $orderNumber);

            // Ajout des articles à la commande
            foreach ($_SESSION['cart'] as $productId => $cartItem) {
                $this->orderRepository->addOrderItem($orderId, $productId, $cartItem['quantity']);

                // Mise à jour du stock
                $this->productRepository->updateStock($productId, -$cartItem['quantity']);
            }

            // Vider le panier après la commande
            $_SESSION['cart'] = [];

            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode([
                    'success' => true,
                    'message' => 'Commande créée avec succès',
                    'orderId' => $orderId,
                    'orderNumber' => $orderNumber
                ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Erreur lors de la création de la commande']));
        }
    }

    /**
     * Récupère les articles du panier avec les détails des produits
     */
    private function getCartItems(): array
    {
        $this->initializeCart();
        $cartItems = [];

        foreach ($_SESSION['cart'] as $productId => $cartItem) {
            $product = $this->productRepository->findById($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $cartItem['quantity'],
                    'subtotal' => $product->price * $cartItem['quantity'],
                    'added_at' => $cartItem['added_at']
                ];
            }
        }

        return $cartItems;
    }

    /**
     * Calcule le total du panier
     */
    private function calculateCartTotal(array $cartItems = null): float
    {
        if ($cartItems === null) {
            $cartItems = $this->getCartItems();
        }

        $total = 0.0;
        foreach ($cartItems as $item) {
            $total += $item['subtotal'];
        }

        return $total;
    }

    /**
     * Compte le nombre total d'articles dans le panier
     */
    private function getCartItemCount(array $cartItems = null): int
    {
        if ($cartItems === null) {
            $this->initializeCart();
            $cartItems = $_SESSION['cart'];
        } else {
            // Si on passe les items détaillés, on compte différemment
            $count = 0;
            foreach ($cartItems as $item) {
                $count += $item['quantity'];
            }
            return $count;
        }

        $count = 0;
        foreach ($cartItems as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Génère un numéro de commande unique
     */
    private function generateOrderNumber(): int
    {
        // Génère un numéro basé sur le timestamp et un nombre aléatoire
        return (int) (time() . rand(100, 999));
    }

    /**
     * Retourne le contenu du panier au format JSON (pour AJAX)
     */
    public function getCartJson(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        if (empty($user)){
            throw new UnauthorizedException();
        }

        $cartItems = $this->getCartItems();
        $cartTotal = $this->calculateCartTotal($cartItems);
        $cartCount = $this->getCartItemCount($cartItems);

        return new Response(200, ['Content-Type' => 'application/json'],
            json_encode([
                'success' => true,
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal,
                'cartCount' => $cartCount
            ]));
    }
}