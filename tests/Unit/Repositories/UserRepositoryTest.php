<?php

namespace App\Tests\Unit\Repositories;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserRepositoryTest extends TestCase
{
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);

        $this->userRepository = new UserRepository($registry);
    }

    public function testUpgradePassword()
    {
        $user = $this->createMock(User::class);
        $newHashedPassword = 'new_hashed_password';

        $user->expects($this->once())
            ->method('setPassword')
            ->with($newHashedPassword);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Mock ClassMetadata and initialize $name property
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'App\Entity\User';
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $this->userRepository->upgradePassword($user, $newHashedPassword);
    }

    public function testUpgradePasswordUnsupportedUser()
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $newHashedPassword = 'new_hashed_password';

        $this->userRepository->upgradePassword($unsupportedUser, $newHashedPassword);
    }

//     public function testCountUsers()
//     {
//         $classMetadata = $this->createMock(ClassMetadata::class);
//         $classMetadata->name = 'App\Entity\User';

//         $qb = $this->createMock(QueryBuilder::class);
//         $query = $this->createMock(Query::class);

//         $qb->expects($this->once())
//             ->method('select')
//             ->with('count(u.id)')
//             ->willReturn($qb);

//         $qb->expects($this->once())
//             ->method('from')
//             ->with($classMetadata->name, 'u')
//             ->willReturn($qb);

//         $qb->expects($this->once())
//             ->method('getQuery')
//             ->willReturn($query);

//         $query->expects($this->once())
//             ->method('getSingleScalarResult')
//             ->willReturn(5);

//         $this->entityManager->expects($this->once())
//             ->method('createQueryBuilder')
//             ->willReturn($qb);

//         $this->entityManager->expects($this->once())
//             ->method('getClassMetadata')
//             ->with('App\Entity\User')
//             ->willReturn($classMetadata);

//         $result = $this->userRepository->countUsers();

//         $this->assertEquals(5, $result);
//     }
}
