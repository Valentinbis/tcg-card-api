<?php

namespace App\Security;

use App\Entity\User;
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
        private TokenManager $tokenManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') &&
            str_contains($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $identifier = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        
        return new SelfValidatingPassport(
            new UserBadge($identifier, function($apiToken) {
                // La résolution du user se fait via UserProvider
                // Mais on peut pré-valider ici si besoin
                return $apiToken;
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
            'error' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}