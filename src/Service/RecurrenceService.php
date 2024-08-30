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
        // Récupérer toutes les récurrences actives
        $recurrences = $this->recurrenceRepository->findActiveRecurrences();

        foreach ($recurrences as $recurrence) {
            $this->entityManager->beginTransaction();
            try {
                // Générer les mouvements pour chaque récurrence
                $this->generateMovementsForRecurrence($recurrence);
                $this->entityManager->commit();
            } catch (\Exception $e) {
                // En cas d'erreur, annuler la transaction et loguer l'erreur
                $this->entityManager->rollback();
                $this->logger->error('An error occurred while generating recurring movements: ' . $e->getMessage());
                throw $e;
            }
        }

        // Loguer le succès de la génération des mouvements
        $this->logger->info('Recurring movements for the next month have been generated successfully.');
    }

    private function generateMovementsForRecurrence(Recurrence $recurrence)
    {
        $now = new \DateTimeImmutable();
        $nextMonth = (clone $now)->modify('+1 month');
        $lastGeneratedDate = $recurrence->getLastGeneratedDate() ?: $recurrence->getStartDate();

        // Générer les mouvements jusqu'à la fin du mois prochain
        while ($this->shouldGenerateMovement($recurrence, $lastGeneratedDate, $nextMonth)) {
            $nextGenerationDate = $this->getNextGenerationDate($recurrence, $lastGeneratedDate);
            $lastMovement = $this->getLastMovement($recurrence);
            // Créer un nouveau mouvement basé sur le dernier mouvement
            $movement = new Movement();
            $movement->setAmount($lastMovement->getAmount());
            $movement->setDate($nextGenerationDate);
            $movement->setRecurrence($recurrence);
            $movement->setDescription($lastMovement->getDescription() ?: '');
            $movement->setType($lastMovement->getType());
            $movement->setUser($lastMovement->getUser());
            $movement->setCategory($lastMovement->getCategory());
            $this->entityManager->persist($movement);

            // Mettre à jour la dernière date de génération
            $lastGeneratedDate = $nextGenerationDate;
            $recurrence->setLastGeneratedDate($lastGeneratedDate);
            $recurrence->setUpdatedAt($now);
        }

        // Persist et flush la récurrence mise à jour après avoir traité tous les mouvements pour cette récurrence
        $this->entityManager->persist($recurrence);
        $this->entityManager->flush();
    }

    private function shouldGenerateMovement(Recurrence $recurrence, \DateTimeImmutable $lastGeneratedDate, \DateTimeImmutable $now): bool
    {
        $nextGenerationDate = $this->getNextGenerationDate($recurrence, $lastGeneratedDate);
        // Vérifier si la prochaine date de génération est avant la date actuelle
        return $now > $nextGenerationDate;
    }

    private function getNextGenerationDate(Recurrence $recurrence, \DateTimeImmutable $lastGeneratedDate): \DateTimeImmutable
    {
        $frequency = $recurrence->getName();
        $interval = match ($frequency) {
            RecurrenceEnum::Daily->value => new \DateInterval('P1D'),
            RecurrenceEnum::Weekly->value => new \DateInterval('P1W'),
            RecurrenceEnum::Bimonthly->value => new \DateInterval('P2M'),
            RecurrenceEnum::Quarterly->value => new \DateInterval('P3M'),
            RecurrenceEnum::Monthly->value => new \DateInterval('P1M'),
            RecurrenceEnum::Yearly->value => new \DateInterval('P1Y'),
            default => throw new \InvalidArgumentException("Unknown frequency: $frequency"),
        };

        // Calculer la prochaine date de génération en ajoutant l'intervalle à la dernière date générée
        return (clone $lastGeneratedDate)->add($interval);
    }

    private function getLastMovement(Recurrence $recurrence): Movement
    {
        // Récupérer le dernier mouvement pour une récurrence donnée
        return $this->entityManager->getRepository(Movement::class)
            ->findOneBy(['recurrence' => $recurrence], ['date' => 'DESC']);
    }
}