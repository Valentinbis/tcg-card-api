<?php

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\Attribute\LogSecurity;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/api/me', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[LogAction('view_profile', 'User profile accessed')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/me', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[LogSecurity('verify_token', 'Token verification requested')]
    public function getCurrentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles()
        ], Response::HTTP_OK);
    }

    #[Route('/api/users', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[LogAction('list_users', 'Users list retrieved')]
    #[LogPerformance(threshold: 0.3)]
    public function users(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/{id}', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[LogAction('view_user', 'User details accessed')]
    public function user(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/{id}', methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN")]
    #[LogAction('delete_user', 'User deleted', 'warning')]
    #[LogSecurity('delete_user', 'User deletion performed', 'warning')]
    public function deleteUser(User $user): JsonResponse
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user/{id}', name: "updateUser", methods: ['PUT'])]
    #[IsGranted("ROLE_USER")]
    #[LogAction('update_user', 'User updated')]
    public function update(Request $request, User $user): JsonResponse
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

            return new Response('User updated!', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
