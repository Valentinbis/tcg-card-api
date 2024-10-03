<?php

namespace App\Controller\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    ) {}

    #[Route('/api/me', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        /** @var User $user */
        $this->logger->info('Fetching current user details', ['user_id' => $user->getId()]);
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/users', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function users(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $this->logger->info('Users fetched successfully', ['count' => count($users)]);
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/{id}', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function user(User $user): JsonResponse
    {
        $this->logger->info('Fetching user details', ['user_id' => $user->getId()]);
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/{id}', methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteUser(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->logger->info('User deleted successfully', ['user_id' => $user->getId()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/user/{id}', name: "updateUser", methods: ['PUT'])]
    #[IsGranted("ROLE_USER")]
    public function update(Request $request, User $user): JsonResponse
    {
        $updatedUser = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );
        $this->entityManager->persist($updatedUser);
        $this->entityManager->flush();
        $this->logger->info('User updated successfully', ['user_id' => $updatedUser->getId()]);

        return new Response('User updated!', Response::HTTP_OK);
    }
}
