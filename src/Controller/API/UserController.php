<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\UserRepository;
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
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }


    #[Route('/api/me', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/users', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function users(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

    #[Route('/api/user/{id}', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function user(User $user): JsonResponse
    {
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

        return new Response('User updated!', Response::HTTP_OK);
    }
}
