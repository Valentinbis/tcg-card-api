<?php

namespace App\Service;

use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Enums\RecurrenceEnum;
use App\Repository\RecurrenceRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RecurrenceService
{
    private $recurrenceRepository;
    private $entityManager;
    private $logger;

    public function __construct(RecurrenceRepository $recurrenceRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->recurrenceRepository = $recurrenceRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function createRecurrence(Movement $movement, array $data): void
    {
        if (isset($data['recurrence']['name']) && RecurrenceEnum::from($data['recurrence']['name'])) {
            $recurrence = $this->updateDataRecurrence($data['recurrence']);
            $movement->setRecurrence($recurrence);
        } else {
            unset($data['recurrence']);
        }
    }

    public function updateRecurrence(Movement $movement, array $data): void
    {
        $recurrence = $movement->getRecurrence();
        if ($recurrence) {
            $this->updateDataRecurrence($data['recurrence'], $recurrence);
        }
    }

    public function updateDataRecurrence(array $recurrenceData, Recurrence $recurrence = null): Recurrence
    {
        if (!$recurrence) {
            $recurrence = new Recurrence();
        }
        if (isset($recurrenceData['name'])) {
            $recurrence->setName($recurrenceData['name']);
        }
        if (isset($recurrenceData['startDate'])) {
            $recurrence->setStartDate($this->formatDate($recurrenceData['startDate']));
        }
        if (isset($recurrenceData['endDate'])) {
            $recurrence->setEndDate($this->formatDate($recurrenceData['endDate']));
        }

        return $recurrence;
    }

    public function formatDate($date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('d/m/Y', $date);
    }

    public function generateNextMonthMovements()
    {
        $recurrences = $this->recurrenceRepository->findActiveRecurrences();
        
        
        try {
            foreach ($recurrences as $recurrence) {
                $this->entityManager->beginTransaction();
                $now = new \DateTimeImmutable();
                $nextMonth = (clone $now)->modify('+1 month');
                $lastGeneratedDate = $recurrence->getLastGeneratedDate() ?: $recurrence->getStartDate();
                
                while ($this->shouldGenerateMovement($recurrence, $lastGeneratedDate, $nextMonth)) {
                    $nextGenerationDate = $this->getNextGenerationDate($recurrence, $lastGeneratedDate);
                    $lastMovement = $this->getLastMovement($recurrence);

                    $movement = new Movement();
                    $movement->setAmount($lastMovement->getAmount());
                    $movement->setDate($nextGenerationDate);
                    $movement->setRecurrence($recurrence);
                    $movement->setDescription($lastMovement->getDescription());
                    $movement->setType($lastMovement->getType());
                    $movement->setUser($lastMovement->getUser());
                    $movement->setCategory($lastMovement->getCategory());
                    $this->entityManager->persist($movement);

                    // Mettre à jour la dernière date de génération
                    $lastGeneratedDate = $this->getNextGenerationDate($recurrence, $lastGeneratedDate);

                    $recurrence->setLastGeneratedDate($lastGeneratedDate);
                }
                // Persist et flush la récurrence mise à jour après avoir traité tous les mouvements pour cette récurrence
                $this->entityManager->persist($recurrence);
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('An error occurred while generating recurring movements: ' . $e->getMessage());
            throw $e;
        }

        $this->logger->info('Recurring movements for the next month have been generated successfully.');
    }

    private function shouldGenerateMovement($recurrence, $lastGeneratedDate, $now)
    {
        $nextGenerationDate = $this->getNextGenerationDate($recurrence, $lastGeneratedDate);

        return $now > $nextGenerationDate;
    }

    private function getNextGenerationDate($recurrence, $lastGeneratedDate)
    {
        $frequency = $recurrence->getName();
        $interval = null;

        switch ($frequency) {
            case RecurrenceEnum::Daily->value:
                // P1D = Period 1 Day
                $interval = new \DateInterval('P1D');
                break;
            case RecurrenceEnum::Weekly->value:
                // P1W = Period 1 Week
                $interval = new \DateInterval('P1W');
                break;
            case RecurrenceEnum::Bimonthly->value:
                // P2M = Period 2 Month
                $interval = new \DateInterval('P2M');
                break;
            case RecurrenceEnum::Quarterly->value:
                // P3M = Period 3 Month
                $interval = new \DateInterval('P3M');
                break;
            case RecurrenceEnum::Monthly->value:
                // P1M = Period 1 Month
                $interval = new \DateInterval('P1M');
                break;
            case RecurrenceEnum::Yearly->value:
                // P1Y = Period 1 Year
                $interval = new \DateInterval('P1Y');
                break;
            default:
                throw new \InvalidArgumentException("Unknown frequency: $frequency");
        }

        return (clone $lastGeneratedDate)->add($interval);
    }

    private function getLastMovement($recurrence)
    {
        return $this->entityManager->getRepository(Movement::class)
            ->findOneBy(['recurrence' => $recurrence], ['date' => 'DESC']);
    }
}
