<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Entity\Card;
use App\Entity\User;
use App\Entity\UserCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Ajoute une carte à la collection de l'utilisateur.
     */
    #[Route('/api/user/cards', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('add_card', 'Card added to collection')]
    public function addCard(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['cardId'])) {
                return $this->json(['error' => 'cardId est requis'], Response::HTTP_BAD_REQUEST);
            }

            $card = $this->entityManager->getRepository(Card::class)->find($data['cardId']);
            
            if (!$card) {
                return $this->json(['error' => 'Carte non trouvée'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier si l'utilisateur possède déjà cette carte
            $existingUserCard = $this->entityManager->getRepository(UserCard::class)->findOneBy([
                'user_id' => $user->getId(),
                'card_id' => $card->getId(),
            ]);

            if ($existingUserCard) {
                return $this->json([
                    'message' => 'Carte déjà dans la collection',
                    'userCard' => [
                        'userId' => $existingUserCard->getUserId(),
                        'cardId' => $existingUserCard->getCardId(),
                    ],
                ], Response::HTTP_OK);
            }

            // Créer une nouvelle entrée
            $userCard = new UserCard();
            $userCard->setUserId($user->getId());
            $userCard->setCardId($card->getId());
            
            if (isset($data['languages']) && is_array($data['languages'])) {
                $userCard->setLanguages($data['languages']);
            }

            $this->entityManager->persist($userCard);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Carte ajoutée à la collection',
                'userCard' => [
                    'userId' => $userCard->getUserId(),
                    'cardId' => $userCard->getCardId(),
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Impossible d\'ajouter la carte',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime une carte de la collection de l'utilisateur.
     */
    #[Route('/api/user/cards/{cardId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('remove_card', 'Card removed from collection')]
    public function removeCard(int $cardId): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $userCard = $this->entityManager->getRepository(UserCard::class)->findOneBy([
                'user_id' => $user->getId(),
                'card_id' => $cardId,
            ]);

            if (!$userCard) {
                return $this->json(['error' => 'Carte non trouvée dans votre collection'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($userCard);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Carte supprimée de la collection',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Impossible de supprimer la carte',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Met à jour les langues d'une carte dans la collection.
     */
    #[Route('/api/user/cards/{cardId}', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_card_languages', 'Card languages updated')]
    public function updateCardLanguages(int $cardId, Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $userCard = $this->entityManager->getRepository(UserCard::class)->findOneBy([
                'user_id' => $user->getId(),
                'card_id' => $cardId,
            ]);

            if (!$userCard) {
                return $this->json(['error' => 'Carte non trouvée dans votre collection'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['languages']) && is_array($data['languages'])) {
                $userCard->setLanguages($data['languages']);
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Carte mise à jour',
                'userCard' => [
                    'userId' => $userCard->getUserId(),
                    'cardId' => $userCard->getCardId(),
                    'languages' => $userCard->getLanguages(),
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Impossible de mettre à jour la carte',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
