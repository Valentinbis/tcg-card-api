<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\DTO\LoginRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\APIAuthenticator;
use App\Service\TokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
        private TokenManager $tokenManager
    ) {
    }

    /**
     * Inscription d'un nouvel utilisateur.
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

        // set user data
        $user = $data;

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $data->getPassword()
            ),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Générer le token avec expiration pour le nouvel utilisateur
        $this->tokenManager->generateToken($user);

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
                'groups' => ['user.show'],
            ]
        );
    }

    /**
     * Connexion d'un utilisateur et génération d'un token API.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[OA\RequestBody(content: new Model(type: LoginRequestDTO::class))]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Deserializer le LoginRequestDTO depuis le body
        try {
            /** @var LoginRequestDTO $loginData */
            $loginData = $this->serializer->deserialize(
                $request->getContent(),
                LoginRequestDTO::class,
                'json'
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid request data'], Response::HTTP_BAD_REQUEST);
        }

        // Trouver l'utilisateur par son nom d'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $loginData->email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $loginData->password)) {
            $this->logger->warning('Invalid login attempt', ['email' => $loginData->email]);

            return new JsonResponse(['error' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un nouveau token avec expiration via TokenManager
        $this->tokenManager->generateToken($user);
        $this->logger->info('User logged in successfully', ['user_id' => $user->getId()]);

        // Renvoyer le token à l'utilisateur
        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['user.show', 'user.token'],
            ]
        );
    }

    /**
     * Déconnexion d'un utilisateur et invalidation du token.
     */
    #[Route('/api/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        // Récupérer le token de l'en-tête Authorization
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $this->logger->warning('Logout attempt without Authorization header');

            return new JsonResponse(['error' => 'No authorization header provided'], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        // Trouver l'utilisateur associé au token
        $user = $this->userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            $this->logger->warning('Logout attempt with invalid token');

            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        // Révoquer le token via TokenManager
        $this->tokenManager->revokeToken($user);
        $this->logger->info('User logged out successfully', ['user_id' => $user->getId()]);

        return new JsonResponse(['message' => 'Logout successful'], Response::HTTP_OK);
    }

    /**
     * Rafraîchir le token (prolonger la session).
     */
    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): Response
    {
        // Récupérer le token de l'en-tête Authorization
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $this->logger->warning('Token refresh attempt without Authorization header');

            return new JsonResponse(['error' => 'No authorization header provided'], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        // Trouver l'utilisateur associé au token
        $user = $this->userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si le token est encore valide (pas complètement expiré)
        if ($user->isTokenExpired()) {
            return new JsonResponse(['error' => 'Token expired, please login again'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un nouveau token avec nouvelle expiration
        $this->tokenManager->refreshToken($user);
        $this->logger->info('Token refreshed successfully', ['user_id' => $user->getId()]);

        // Renvoyer le nouveau token
        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['user.show', 'user.token'],
            ]
        );
    }
}
