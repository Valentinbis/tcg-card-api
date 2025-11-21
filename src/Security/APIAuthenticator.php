<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class APIAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private TokenManager $tokenManager,
        private UserRepository $userRepository
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $authHeader = $this->getAuthorizationHeader($request);

        return $authHeader && str_contains($authHeader, 'Bearer ');
    }

    private function getAuthorizationHeader(Request $request): ?string
    {
        return $request->headers->get('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $this->getAuthorizationHeader($request);

        if (!$authHeader) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $apiToken = str_replace('Bearer ', '', $authHeader);

        if (empty($apiToken)) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function ($apiToken) {
                $user = $this->userRepository->findOneBy(['apiToken' => $apiToken]);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Invalid API token');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            throw new CustomUserMessageAuthenticationException('Invalid user');
        }

        // Vérifier si le token est expiré
        if (!$this->tokenManager->isTokenValid($user)) {
            throw new CustomUserMessageAuthenticationException('Token expiré ou inactivité trop longue');
        }

        // Mettre à jour la dernière activité
        $this->tokenManager->updateActivity($user);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
