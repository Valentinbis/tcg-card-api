<?php

namespace App\Service;

use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Enums\RecurrenceEnum;
use Carbon\CarbonImmutable;

class RecurrenceService
{
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
}
