<?php

namespace App\Service;

use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Enums\RecurrenceEnum;
use Carbon\CarbonImmutable;

class RecurrenceService
{
    public function createOrUpdateRecurrence(Movement $movement, array $data)
    {
        $recurrence = $movement->getRecurrence();
        if (!$recurrence && isset($data['recurrence']) && RecurrenceEnum::from($data['recurrence']['name'])) {
            $recurrence = new Recurrence();
            $movement->setRecurrence($recurrence);
        }

        if ($recurrence) {
            $recurrenceData = $data['recurrence'];

            if (isset($recurrenceData['name'])) {
                $recurrence->setName($recurrenceData['name']);
            }

            // Convertit les dates de chaîne en objets CarbonImmutable
            if (isset($recurrenceData['startDate'])) {
                $recurrence->setStartDate(CarbonImmutable::createFromFormat('d/m/Y', $recurrenceData['startDate']));
            }
            if (isset($recurrenceData['endDate'])) {
                $recurrence->setEndDate(CarbonImmutable::createFromFormat('d/m/Y', $recurrenceData['endDate']));
            }
        } else {
            $movement->setRecurrence(null);
        }
    }
}
