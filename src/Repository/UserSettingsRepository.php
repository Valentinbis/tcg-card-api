<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSettings>
 */
class UserSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSettings::class);
    }

    public function findByUser(User $user): ?UserSettings
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function findOrCreateForUser(User $user): UserSettings
    {
        $settings = $this->findByUser($user);

        if ($settings === null) {
            $settings = new UserSettings();
            $settings->setUser($user);
            $this->getEntityManager()->persist($settings);
            $this->getEntityManager()->flush();
        }

        return $settings;
    }

    public function save(UserSettings $settings): void
    {
        $settings->setUpdatedAt(new \DateTime());
        $this->getEntityManager()->persist($settings);
        $this->getEntityManager()->flush();
    }
}
