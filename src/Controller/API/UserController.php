<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\Attribute\LogSecurity;
use App\Entity\User;
use App\Repository\UserSettingsRepository;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserStatsService $userStatsService,
        private readonly UserSettingsRepository $userSettingsRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Récupère le profil de l'utilisateur connecté.
     */
    #[Route('/api/me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_profile', 'User profile accessed')]
    #[LogSecurity('verify_token', 'Token verification requested')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show', 'user.token'],
        ]);
    }

    /**
     * Liste tous les utilisateurs.
     */
    #[Route('/api/users', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('list_users', 'Users list retrieved')]
    #[LogPerformance(threshold: 0.3)]
    public function users(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => ['user.show'],
        ]);
    }

    /**
     * Récupère les détails d'un utilisateur par son ID.
     */
    #[Route('/api/user/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_user', 'User details accessed')]
    public function user(#[MapEntity] User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show'],
        ]);
    }

    /**
     * Supprime un utilisateur (admin uniquement).
     */
    #[Route('/api/user/{id}', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[LogAction('delete_user', 'User deleted', 'warning')]
    #[LogSecurity('delete_user', 'User deletion performed', 'warning')]
    public function deleteUser(#[MapEntity] User $user): Response|JsonResponse
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modifie les informations d'un utilisateur.
     */
    #[Route('/api/user/{id}', name: 'updateUser', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_user', 'User updated')]
    public function update(Request $request, #[MapEntity] User $user): JsonResponse
    {
        try {
            $updatedUser = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
            );

            $this->entityManager->persist($updatedUser);
            $this->entityManager->flush();

            return $this->json(['message' => 'User updated!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupère les statistiques de l'utilisateur connecté.
     */
    #[Route('/api/user/stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_stats', 'User statistics accessed')]
    #[LogPerformance(threshold: 0.5)]
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json($this->userStatsService->getUserStats($user), Response::HTTP_OK);
    }

    /**
     * Met à jour le profil de l'utilisateur connecté.
     */
    #[Route('/api/user/profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_profile', 'User profile updated')]
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $data = json_decode($request->getContent(), true);

            if (isset($data['firstName'])) {
                $user->setFirstName($data['firstName']);
            }
            if (isset($data['lastName'])) {
                $user->setLastName($data['lastName']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }

            $this->entityManager->flush();

            return $this->json(['message' => 'Profil mis à jour avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Échec de la mise à jour du profil'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change le mot de passe de l'utilisateur connecté.
     */
    #[Route('/api/user/password', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('change_password', 'User password changed')]
    #[LogSecurity('password_change', 'Password modification performed', 'warning')]
    public function changePassword(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $data = json_decode($request->getContent(), true);

            // Vérifier l'ancien mot de passe
            if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'] ?? '')) {
                return $this->json(['error' => 'Mot de passe actuel incorrect'], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier que les nouveaux mots de passe correspondent
            if (($data['newPassword'] ?? '') !== ($data['confirmPassword'] ?? '')) {
                return $this->json(['error' => 'Les nouveaux mots de passe ne correspondent pas'], Response::HTTP_BAD_REQUEST);
            }

            // Hasher et sauvegarder le nouveau mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
            $user->setPassword($hashedPassword);

            $this->entityManager->flush();

            return $this->json(['message' => 'Mot de passe modifié avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Échec du changement de mot de passe'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupère les paramètres de l'utilisateur.
     */
    #[Route('/api/user/settings', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_settings', 'User settings accessed')]
    public function getSettings(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $settings = $this->userSettingsRepository->findOrCreateForUser($user);
        
        return $this->json($settings, Response::HTTP_OK, [], [
            'groups' => ['settings.show'],
        ]);
    }

    /**
     * Met à jour les paramètres de l'utilisateur.
     */
    #[Route('/api/user/settings', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_settings', 'User settings updated')]
    public function saveSettings(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $settings = $this->userSettingsRepository->findOrCreateForUser($user);
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
            }

            // Mise à jour des paramètres d'affichage
            if (isset($data['cardsPerPage'])) {
                $settings->setCardsPerPage((int) $data['cardsPerPage']);
            }
            if (isset($data['defaultView'])) {
                $settings->setDefaultView($data['defaultView']);
            }
            if (isset($data['defaultLanguage'])) {
                $settings->setDefaultLanguage($data['defaultLanguage']);
            }
            if (isset($data['showCardNumbers'])) {
                $settings->setShowCardNumbers((bool) $data['showCardNumbers']);
            }
            if (isset($data['showPrices'])) {
                $settings->setShowPrices((bool) $data['showPrices']);
            }

            // Mise à jour des notifications
            if (isset($data['emailNotifications'])) {
                $settings->setEmailNotifications((bool) $data['emailNotifications']);
            }
            if (isset($data['newCardAlerts'])) {
                $settings->setNewCardAlerts((bool) $data['newCardAlerts']);
            }
            if (isset($data['priceDropAlerts'])) {
                $settings->setPriceDropAlerts((bool) $data['priceDropAlerts']);
            }
            if (isset($data['weeklyReport'])) {
                $settings->setWeeklyReport((bool) $data['weeklyReport']);
            }

            // Mise à jour des paramètres de confidentialité
            if (isset($data['profileVisibility'])) {
                $settings->setProfileVisibility($data['profileVisibility']);
            }
            if (isset($data['showCollection'])) {
                $settings->setShowCollection((bool) $data['showCollection']);
            }
            if (isset($data['showWishlist'])) {
                $settings->setShowWishlist((bool) $data['showWishlist']);
            }

            // Validation des données
            $errors = $this->validator->validate($settings);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json([
                    'error' => 'Validation échouée',
                    'details' => $errorMessages,
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->userSettingsRepository->save($settings);

            return $this->json([
                'message' => 'Paramètres mis à jour avec succès',
                'settings' => $settings,
            ], Response::HTTP_OK, [], [
                'groups' => ['settings.show'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Échec de la mise à jour des paramètres',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
