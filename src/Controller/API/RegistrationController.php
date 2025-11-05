<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\APIAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    /**
     * Inscription d'un nouvel utilisateur
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, APIAuthenticator $authenticator): Response
    {
        if (empty($request->getContent())) {
            $this->logger->warning('Registration attempt with empty request body');
            return new Response('The request is empty', 400);
        }
        $data = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            $this->logger->warning('Registration attempt with invalid data', ['errors' => (string) $errors]);
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
        $this->logger->info('User registered successfully', ['user_id' => $user->getId()]);

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

    /**
     * Connexion d'un utilisateur et génération d'un token API
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['email']) || empty($data['password'])) {
            $this->logger->warning('Login attempt with missing email or password');
            return new JsonResponse(['error' => 'Missing email or password'], Response::HTTP_BAD_REQUEST);
        }
        // Trouver l'utilisateur par son nom d'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            $this->logger->warning('Invalid login attempt', ['email' => $data['email']]);
            return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un nouveau token Bearer
        $token = bin2hex(random_bytes(60));

        // Stocker le token en base de données
        $user->setApiToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->logger->info('User logged in successfully', ['user_id' => $user->getId()]);

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

    /**
     * Déconnexion d'un utilisateur et invalidation du token
     */
    #[Route('/api/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        // Récupérer le token de l'en-tête Authorization
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        // Trouver l'utilisateur associé au token
        $user = $this->userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            $this->logger->warning('Logout attempt with invalid token');
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        // Invalider le token
        $user->setApiToken('');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->logger->info('User logged out successfully', ['user_id' => $user->getId()]);

        return new JsonResponse(['message' => 'Logout successful'], Response::HTTP_OK);
    }
}
