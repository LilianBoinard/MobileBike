<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Model\Product\MobileBike\MobileBike;
use MobileBike\App\Model\Product\MobileBike\Type\Fairing;
use MobileBike\App\Model\Product\MobileBike\Type\Recumbent;
use MobileBike\App\Model\Product\MobileBike\Type\Special;
use MobileBike\App\Model\Product\MobileBike\Type\Trikes;
use MobileBike\App\Model\Product\MobileBike\Type\Used;
use MobileBike\App\Model\Product\SparePart\SparePart;
use MobileBike\App\Repository\Product\ProductRepository;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Exception\Exceptions\ImageUploadException;
use MobileBike\Core\Exception\Exceptions\NotFoundException;
use MobileBike\Core\Exception\Exceptions\UnauthorizedException;
use MobileBike\Core\Service\ImageUploadService;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardProductsController extends AbstractController
{
    private UserRepository $userRepository;
    private ProductRepository $productRepository;
    private ImageUploadService $imageUploadService;

    // Mapping des types de MobileBike
    private const MOBILE_BIKE_TYPES = [
        'used' => Used::class,
        'trikes' => Trikes::class,
        'recumbent' => Recumbent::class,
        'fairing' => Fairing::class,
        'special' => Special::class,
    ];

    public function __construct(
        View               $view,
        AuthenticationInterface $authentication,
        UserRepository     $userRepository,
        ProductRepository  $productRepository,
        ImageUploadService $imageUploadService
    )
    {
        $this->view = $view;
        $this->authentication = $authentication;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->imageUploadService = $imageUploadService;
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin || !$user) {
            throw new UnauthorizedException();
        }

        $products = $this->productRepository->findAll();

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/products/products.html.twig', [
                'user' => $user,
                'isClient' => true,
                'isAdmin' => true,
                'products' => $products
            ])
        );
    }

    public function displayEditProductPage(ServerRequestInterface $request, $arrayParams): ResponseInterface
    {
        $productId = $arrayParams['id'];
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundException();
        }

        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        // Déterminer le type de produit pour charger le bon template
        $productType = $this->productRepository->getProductType($productId);
        $template = $product instanceof SparePart ? 'editSparePart.html.twig' : 'editMobileBike.html.twig';

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig("dashboard/products/{$template}", [
                'product' => $product,
                'productType' => $productType,
                'mobileBikeTypes' => array_keys(self::MOBILE_BIKE_TYPES),
                'user' => $user,
                'isClient' => true,
                'isAdmin' => true
            ])
        );
    }

    public function displayAddMobileBikePage(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/products/addMobileBike.html.twig', [
                'mobileBikeTypes' => array_keys(self::MOBILE_BIKE_TYPES),
                'user' => $user,
                'isClient' => true,
                'isAdmin' => true
            ])
        );
    }

    public function displayAddSparePartPage(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/products/addSparePart.html.twig', [
                'user' => $user,
                'isClient' => true,
                'isAdmin' => true
            ])
        );
    }

    private function getProductData(ServerRequestInterface $request): array|ResponseInterface
    {
        try {
            // Récupérer les données du formulaire
            $data = $request->getParsedBody();

            // Récupérer les fichiers uploadés
            $uploadedFiles = $request->getUploadedFiles();

            // Traiter l'upload d'image si présent
            $imagePath = null;
            if (isset($uploadedFiles['image']) && $uploadedFiles['image']->getError() === UPLOAD_ERR_OK) {
                $uploadedFile = $uploadedFiles['image'];

                // Créer un fichier temporaire pour PSR-7
                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                $uploadedFile->moveTo($tempFile);

                error_log("Tempfile: " . $tempFile);

                // Convertir UploadedFileInterface en format attendu par ImageUploadService
                $fileData = [
                    'name' => $uploadedFile->getClientFilename(),
                    'type' => $uploadedFile->getClientMediaType(),
                    'tmp_name' => $tempFile,
                    'error' => $uploadedFile->getError(),
                    'size' => $uploadedFile->getSize()
                ];

                // Définir le chemin d'upload correct
                $projectRoot = dirname(__DIR__, 3);
                $this->imageUploadService->setUploadDir($projectRoot . '/public/assets/uploads/products');

                $imagePath = $this->imageUploadService->uploadImage($fileData);

                // Nettoyer le fichier temporaire
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                if (!$imagePath) {
                    throw new ImageUploadException("Erreur lors de l'upload de l'image");
                }
            }

            // Ajouter le chemin de l'image aux données
            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            return $data;

        } catch (\Exception $e) {
            // Log l'erreur générale
            error_log("Erreur lors de l'extraction des données Produit : " . $e->getMessage());

            return new Response(302, [
                'Location' => '/dashboard/products?error=add_failed'
            ]);
        }
    }

    public function saveMobileBike(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        $data = $this->getProductData($request);

        if ($data instanceof ResponseInterface) {
            return $data; // Erreur lors de l'extraction des données
        }

        try {
            // Récupérer le type de MobileBike depuis les données du formulaire
            $mobileBikeType = $data['type'] ?? 'used'; // Par défaut 'used'

            // Vérifier que le type est valide
            if (!isset(self::MOBILE_BIKE_TYPES[$mobileBikeType])) {
                throw new \InvalidArgumentException("Type de MobileBike invalide: {$mobileBikeType}");
            }

            // Créer l'instance du bon type
            $className = self::MOBILE_BIKE_TYPES[$mobileBikeType];
            $product = new $className($data);

            // Sauvegarder le produit
            $success = $this->productRepository->save($product);

            if (!$success) {
                throw new \Exception("Échec de la sauvegarde du produit");
            }

            return new Response(302, ['Location' => '/dashboard/products?success=product_added']);

        } catch (\Exception $e) {
            error_log("Erreur lors de la sauvegarde du MobileBike : " . $e->getMessage());
            return new Response(302, ['Location' => '/dashboard/products?error=add_failed']);
        }
    }

    public function saveSparePart(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        $data = $this->getProductData($request);

        if ($data instanceof ResponseInterface) {
            return $data; // Erreur lors de l'extraction des données
        }

        try {
            // Créer et sauvegarder le produit
            $product = new SparePart($data);
            $success = $this->productRepository->save($product);

            if (!$success) {
                throw new \Exception("Échec de la sauvegarde du produit");
            }

            return new Response(302, ['Location' => '/dashboard/products?success=product_added']);

        } catch (\Exception $e) {
            error_log("Erreur lors de la sauvegarde du SparePart : " . $e->getMessage());
            return new Response(302, ['Location' => '/dashboard/products?error=add_failed']);
        }
    }

    public function editProduct(ServerRequestInterface $request, $arrayParams): ResponseInterface
    {
        $productId = $arrayParams['id'];
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException('Produit non trouvé');
        }

        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        try {
            // Récupérer les données du formulaire
            $data = $request->getParsedBody();

            // Récupérer les fichiers uploadés
            $uploadedFiles = $request->getUploadedFiles();

            // Traiter l'upload d'image si présent
            $imagePath = null;
            if (isset($uploadedFiles['image']) && $uploadedFiles['image']->getError() === UPLOAD_ERR_OK) {
                $uploadedFile = $uploadedFiles['image'];

                // Créer un fichier temporaire pour PSR-7
                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                $uploadedFile->moveTo($tempFile);

                // Convertir UploadedFileInterface en format attendu par ImageUploadService
                $fileData = [
                    'name' => $uploadedFile->getClientFilename(),
                    'type' => $uploadedFile->getClientMediaType(),
                    'tmp_name' => $tempFile,
                    'error' => $uploadedFile->getError(),
                    'size' => $uploadedFile->getSize()
                ];

                // Définir le chemin d'upload correct
                $projectRoot = dirname(__DIR__, 3);
                $this->imageUploadService->setUploadDir($projectRoot . '/public/assets/uploads/products');

                $imagePath = $this->imageUploadService->uploadImage($fileData);

                // Nettoyer le fichier temporaire
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                if (!$imagePath) {
                    throw new ImageUploadException("Erreur lors de l'upload de l'image");
                }
            }

            // Préserver les données existantes et ajouter les nouvelles
            $data['id_product'] = $productId;

            // Si une nouvelle image a été uploadée, l'utiliser, sinon garder l'ancienne
            if ($imagePath) {
                $data['image'] = $imagePath;
            } else {
                // Préserver l'image existante
                $data['image'] = $product->image;
            }

            // Créer le produit du même type que l'original
            $updatedProduct = null;
            if ($product instanceof SparePart) {
                $updatedProduct = new SparePart($data);
            } else {
                // Pour les MobileBikes, détecter le type exact
                $productType = $this->productRepository->getProductType($productId);
                if ($productType && isset(self::MOBILE_BIKE_TYPES[$productType])) {
                    $className = self::MOBILE_BIKE_TYPES[$productType];
                    $updatedProduct = new $className($data);
                } else {
                    // Fallback - utiliser le type depuis le formulaire ou le type existant
                    $mobileBikeType = $data['type'] ?? 'used';
                    $className = self::MOBILE_BIKE_TYPES[$mobileBikeType] ?? Used::class;
                    $updatedProduct = new $className($data);
                }
            }

            if (!$updatedProduct) {
                throw new \Exception("Impossible de déterminer le type de produit");
            }

            $success = $this->productRepository->save($updatedProduct);

            if (!$success) {
                throw new \Exception("Échec de la mise à jour du produit");
            }

            return new Response(302, ['Location' => '/dashboard/products?success=product_edited']);

        } catch (ImageUploadException $e) {
            error_log("Erreur d'upload d'image : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/products/edit/' . $productId . '?error=image_upload_failed'
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors de la sauvegarde du produit : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/products/edit/' . $productId . '?error=save_failed'
            ]);
        }
    }

    public function deleteProduct(ServerRequestInterface $request, $arrayParams): ResponseInterface
    {
        $productId = $arrayParams['id'];
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException();
        }

        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);
        if (!$isAdmin) {
            throw new UnauthorizedException();
        }

        try {
            // Supprimer l'image associée si elle existe
            if (isset($product->image) && $product->image) {
                $this->imageUploadService->deleteImage($product->image);
            }

            $success = $this->productRepository->delete($productId);

            if (!$success) {
                throw new \Exception("Échec de la suppression du produit");
            }

            return new Response(302, ['Location' => '/dashboard/products?success=product_deleted']);

        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression du produit : " . $e->getMessage());
            return new Response(302, ['Location' => '/dashboard/products?error=delete_failed']);
        }
    }
}