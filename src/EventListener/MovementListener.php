<?php
namespace App\EventListener;

use App\Entity\Movement;
use App\Enums\MovementEnum;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class MovementListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // Vérifiez si l'entité est une instance de Movement
        if (!$entity instanceof Movement) {
            return; // Si ce n'est pas le cas, ne faites rien
        }
        // // Si c'est le cas, procédez à la mise à jour du total du mouvement
        $this->updateMovementTotal($entity);
    }
    
    private function updateMovementTotal(Movement $movement, float $oldAmount = 0): void
    {
        $newTotal = $this->calculateNewTotal($movement, $oldAmount);
    
        // Mettre à jour le mouvement avec le nouveau total
        $movement->setBank($newTotal);
    }
    
    private function calculateNewTotal(Movement $movement, float $oldAmount): float
    {
        $type = $movement->getType();
        $amount = $movement->getAmount();
        $bankTotal = $movement->getBank();        
        if ($type === MovementEnum::Expense->value) {
            $newTotal = $bankTotal - $oldAmount - $amount;
        } elseif ($type === MovementEnum::Income->value) {
            $newTotal = $bankTotal - $oldAmount + $amount;
        } else {
            throw new \InvalidArgumentException("Type de mouvement inconnu");
        }
    
        return $newTotal;
    }
}