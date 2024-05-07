<?php
// src/EventListener/MovementListener.php
namespace App\EventListener;

use App\Entity\Movement;
use App\Entity\History;
use App\Entity\Bank;
use App\Enums\MovementEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class MovementListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(LifecycleEventArgs $args)
    {

        $movement = $args->getObject();

        // Vérifiez si l'entité est une instance de Movement
        if (!$movement instanceof Movement) {
            return;
        }

        $bank = $this->entityManager->getRepository(Bank::class)->findOneBy(['user' => $movement->getUser()]);
        if (!$bank) {
            $bank = new Bank();
            $bank->setUser($movement->getUser());
            $bank->setBalance(0);
        }

        // Créer une nouvelle entrée dans la table Historique
        $historique = new History();
        $historique->setMovement($movement);
        $historique->setUser($movement->getUser());

        // Mettre à jour la table Bank
        if ($movement->getType() == MovementEnum::Expense->value) {
            $expense = $bank->getBalance() - $movement->getAmount();
            $bank->setBalance($expense);
            $historique->setBalance($expense);
        } else {
            $income = $bank->getBalance() + $movement->getAmount();
            $bank->setBalance($income);
            $historique->setBalance($income);
        }

        $this->entityManager->persist($historique);
        $this->entityManager->persist($bank);
        $this->entityManager->flush();
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $movement = $args->getObject();

        // Vérifiez si l'entité est une instance de Movement
        if (!$movement instanceof Movement) {
            return;
        }

        $uow = $this->entityManager->getUnitOfWork();
        $changes = $uow->getEntityChangeSet($movement);

        // Vérifiez si le montant a été modifié
        if (!isset($changes['amount'])) {
            return;
        }
        $oldAmount = $changes['amount'][0];
        $newAmount = $changes['amount'][1];

        $bank = $this->entityManager->getRepository(Bank::class)->findOneBy(['user' => $movement->getUser()]);
        if (!$bank) {
            $bank = new Bank();
            $bank->setUser($movement->getUser());
            $bank->setBalance(0);
        }

        // Mettre à jour la table Historique
        $historiques = $this->entityManager->getRepository(History::class)->findBy(['user' => $movement->getUser()], ['createdAt' => 'ASC']);
        $previousBalance = 0;
        $updated = false;
        foreach ($historiques as $historique) {
            if ($updated || $historique->getMovement() === $movement) {
                if ($movement->getType() == MovementEnum::Expense->value) {
                    $previousBalance -= $newAmount;
                } else {
                    $previousBalance += $newAmount;
                }
                $updated = true;
            } else {
                if ($historique->getMovement()->getType() == MovementEnum::Expense->value) {
                    $previousBalance -= $historique->getMovement()->getAmount();
                } else {
                    $previousBalance += $historique->getMovement()->getAmount();
                }
            }

            $historique->setBalance($previousBalance);
            $historique->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($historique);
        }

        // Mettre à jour la table Bank
        $bank->setBalance($previousBalance);
        $bank->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($bank);
        $this->entityManager->flush();
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $movement = $args->getObject();

        if ($movement instanceof Movement) {
            // Effectuez les recalculs nécessaires ici, comme dans votre fonction postUpdate.

            // Supprimez la ligne correspondante dans l'historique.
            $history = $this->entityManager->getRepository(History::class)->findOneBy(['movement' => $entity]);

            if ($history) {
                $this->entityManager->remove($history);
                $this->entityManager->flush();
            }
        }
    }

    private function updateBank(Movement $movement, float $newAmount): Bank
    {
        $bank = $this->entityManager->getRepository(Bank::class)->findOneBy(['user' => $movement->getUser()]);
        if (!$bank) {
            $bank = new Bank();
            $bank->setUser($movement->getUser());
            $bank->setBalance(0);
        }

        $bank->setBalance($bank->getBalance() + $newAmount);
        $bank->setUpdatedAt(new \DateTimeImmutable());

        return $bank;
    }

    private function updateHistory(Movement $movement, float $newAmount): void
    {
        $historiques = $this->entityManager->getRepository(History::class)->findBy(['user' => $movement->getUser()], ['createdAt' => 'ASC']);
        $previousBalance = 0;
        $updated = false;
        foreach ($historiques as $historique) {
            if ($updated || $historique->getMovement() === $movement) {
                $previousBalance = $this->calculateBalance($movement, $newAmount, $previousBalance);
                $updated = true;
            } else {
                $previousBalance = $this->calculateBalance($historique->getMovement(), $historique->getMovement()->getAmount(), $previousBalance);
            }

            $historique->setBalance($previousBalance);
            $historique->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($historique);
        }
    }

    private function calculateBalance(Movement $movement, float $amount, float $previousBalance): float
    {
        if ($movement->getType() == MovementEnum::Expense->value) {
            return $previousBalance - $amount;
        } else {
            return $previousBalance + $amount;
        }
    }
}
