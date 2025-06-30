<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Model\User\User;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Exception\Exceptions\ImageUploadException;
use MobileBike\Core\Exception\Exceptions\UnauthorizedException;
use MobileBike\Core\Service\ImageUploadService;
use MobileBike\Core\View\View;
use Psr\Http\Message\ServerRequestInterface;

class DashboardProfile extends AbstractController
{
    private UserRepository $userRepository;
    private ImageUploadService $imageUploadService;

    public function __construct(View $view, AuthenticationInterface $authentication, UserRepository $userRepository, ImageUploadService $imageUploadService)
    {
        $this->view = $view;
        $this->authentication = $authentication;
        $this->userRepository = $userRepository;
        $this->imageUploadService = $imageUploadService;
    }

    public function index()
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id);

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/editProfile.html.twig', [
                'user' => $user,
                'isClient' => true,
                'isAdmin' => $isAdmin
            ])
        );
    }

    public function update(ServerRequestInterface $request)
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id);

        try {
            // Récupérer les données du formulaire
            $data = $request->getParsedBody();

            // Vérification de sécurité
            if (!$data['id'] || (int)$data['id'] !== $user->id) {
                throw new UnauthorizedException("Vous n'avez pas la permission pour modifier cet utilisateur.");
            }

            // Vérifier l'unicité de l'email et du nom d'utilisateur
            if (!empty($data['email']) && !$this->userRepository->isEmailAvailable($data['email'], $user->id)) {
                throw new \InvalidArgumentException("Cet email est déjà utilisé par un autre utilisateur");
            }

            if (!empty($data['username']) && !$this->userRepository->isUsernameAvailable($data['username'], $user->id)) {
                throw new \InvalidArgumentException("Ce nom d'utilisateur est déjà utilisé par un autre utilisateur");
            }

            // Récupérer les fichiers uploadés
            $uploadedFiles = $request->getUploadedFiles();

            // Traiter l'upload d'image si présent
            $imagePath = null;
            if (isset($uploadedFiles['profile_image']) && $uploadedFiles['profile_image']->getError() === UPLOAD_ERR_OK) {
                $uploadedFile = $uploadedFiles['profile_image'];

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
                $this->imageUploadService->setUploadDir($projectRoot . '/public/assets/uploads/profiles');

                $imagePath = $this->imageUploadService->uploadImage($fileData);

                // Nettoyer le fichier temporaire
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                if (!$imagePath) {
                    throw new ImageUploadException("Erreur lors de l'upload de l'image");
                }
            }

            // Préparer les données pour la mise à jour
            if ($imagePath) {
                $data['profile_image'] = $imagePath;
            } else {
                // Préserver l'image existante
                $data['profile_image'] = $user->profile_image;
            }

            $updatedUser = null;
            if ($user instanceof User) {
                $updatedUser = new User($data);
            }

            if (!$updatedUser) {
                throw new \Exception("Impossible de créer l'objet utilisateur mis à jour");
            }

            $success = $this->userRepository->save($updatedUser);

            if (!$success) {
                throw new \Exception("Échec de la mise à jour de l'utilisateur");
            }

            return new Response(302, ['Location' => '/dashboard/home?success=profile_edited']);

        } catch (ImageUploadException $e) {
            error_log("Erreur d'upload d'image : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/profile/edit?error=image_upload_failed'
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Erreur de validation : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/profile/edit?error=validation_failed&message=' . urlencode($e->getMessage())
            ]);
        } catch (UnauthorizedException $e) {
            error_log("Erreur d'autorisation : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/profile/edit?error=unauthorized'
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors de l'édition de l'utilisateur : " . $e->getMessage());
            return new Response(302, [
                'Location' => '/dashboard/profile/edit?error=save_failed'
            ]);
        }
    }
}