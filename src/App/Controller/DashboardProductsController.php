<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Model\Product\MobileBike\MobileBike;
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

    public function __construct(
        View $view, AuthenticationInterface
             $authentication,
        UserRepository $userRepository,
        ProductRepository $productRepository,
        ImageUploadService $imageUploadService
    ){
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

        $products = $this->productRepository->findAllMobileBikes();

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

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/products/editProduct.html.twig', [
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



    public function saveMobileBike(ServerRequestInterface $request): ResponseInterface
    {
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
                $projectRoot = dirname(__DIR__, 3); // Remontez jusqu'à la racine du projet
                $this->imageUploadService->setUploadDir($projectRoot . '/public/uploads/products');

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

            // Créer et sauvegarder le produit
            $product = new MobileBike($data);
            $this->productRepository->save($product);

            return new Response(302, ['Location' => '/dashboard/products?success=product_added']);

        } catch (ImageUploadException $e) {
            // Log l'erreur d'upload spécifique
            error_log("Erreur d'upload d'image : " . $e->getMessage());

            return new Response(302, [
                'Location' => '/dashboard/products/mobilebike/add?error=image_upload_failed'
            ]);
        } catch (\Exception $e) {
            // Log l'erreur générale
            error_log("Erreur lors de la sauvegarde du produit : " . $e->getMessage());

            return new Response(302, [
                'Location' => '/dashboard/products/mobilebike/add?error=save_failed'
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

        // Supprimer l'image associée si elle existe
        if ($product->image_path) {
            $this->imageUploadService->deleteImage($product->image_path);
        }

        $this->productRepository->delete($productId);

        return new Response(302, ['Location' => '/dashboard/products']);
    }
}