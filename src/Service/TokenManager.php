<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenManager
{
    // Durée de validité du token : 30 jours
    private const TOKEN_LIFETIME = 30 * 24 * 60 * 60;

    // Durée d'inactivité max avant expiration : 7 jours
    private const INACTIVITY_TIMEOUT = 7 * 24 * 60 * 60;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function generateToken(User $user): string
    {
        $token = bin2hex(random_bytes(60));
        $expiresAt = new \DateTimeImmutable('+'.self::TOKEN_LIFETIME.' seconds');

        $user->setApiToken($token);
        $user->setTokenExpiresAt($expiresAt);
        $user->updateLastActivity();

        $this->entityManager->flush();

        return $token;
    }

    public function isTokenValid(User $user): bool
    {
        if ($user->isTokenExpired()) {
            return false;
        }

        $lastActivity = $user->getLastActivityAt();
        if (null === $lastActivity) {
            return false;
        }

        $now = new \DateTimeImmutable();
        $inactivityDuration = $now->getTimestamp() - $lastActivity->getTimestamp();

        return $inactivityDuration < self::INACTIVITY_TIMEOUT;
    }

    public function refreshToken(User $user): string
    {
        return $this->generateToken($user);
    }

    public function updateActivity(User $user): void
    {
        // Ne mettre à jour l'activité que si elle date de plus de 5 minutes
        $lastActivity = $user->getLastActivityAt();
        if (null === $lastActivity) {
            $user->updateLastActivity();
            $this->entityManager->flush();
            return;
        }

        $now = new \DateTimeImmutable();
        $timeSinceLastActivity = $now->getTimestamp() - $lastActivity->getTimestamp();

        // Ne mettre à jour que si ça fait plus de 5 minutes
        if ($timeSinceLastActivity > 300) { // 5 minutes
            $user->updateLastActivity();
            $this->entityManager->flush();
        }
    }

    public function revokeToken(User $user): void
    {
        $user->setApiToken(bin2hex(random_bytes(60)));
        $user->setTokenExpiresAt(null);
        $user->setLastActivityAt(null);

        $this->entityManager->flush();
    }

    public function shouldRefresh(User $user): bool
    {
        $expiresAt = $user->getTokenExpiresAt();
        if (null === $expiresAt) {
            return true;
        }

        $now = new \DateTimeImmutable();
        $remainingTime = $expiresAt->getTimestamp() - $now->getTimestamp();

        return $remainingTime < (3 * 24 * 60 * 60);
    }
}
