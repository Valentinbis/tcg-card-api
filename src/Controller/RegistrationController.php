<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\APIAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $serializer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, APIAuthenticator $authenticator): Response
    {
        if (empty($request->getContent())) {
            return new Response('The request is empty', 400);
        }
        $data = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        //set user data
        $user = $data;

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $data->getPassword()
            ),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $authenticateUser = $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        if ($authenticateUser instanceof Response) {
            return $authenticateUser;
        }

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['user.show']
            ]
        );
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Missing email or password'], Response::HTTP_BAD_REQUEST);
        }
        // Trouver l'utilisateur par son nom d'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un nouveau token Bearer
        $token = bin2hex(random_bytes(60));

        // Stocker le token en base de données
        $user->setApiToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Renvoyer le token à l'utilisateur
        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['user.show', 'user.token']
            ]
        );
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        // Récupérer le token de l'en-tête Authorization
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        // Trouver l'utilisateur associé au token
        $user = $this->userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        // Invalider le token
        $user->setApiToken('');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Logout successful'], Response::HTTP_OK);
    }
}
