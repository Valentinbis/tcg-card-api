<?php
namespace App\Service;

use App\Entity\Recurrence;
use App\Enums\RecurrenceEnum;

class RecurrenceService
{
    public function createRecurrence($movement, $data)
    {
        // Vérifie si 'recurrence' est défini et si le nom est valide
        if (isset($data['recurrence']) && RecurrenceEnum::from($data['recurrence']['name'])) {
            $recurrenceData = $data['recurrence'];
            $recurrence = new Recurrence();
            $recurrence->setName($recurrenceData['name']);

            // Convertit les dates de chaîne en objets DateTimeImmutable
            if (isset($recurrenceData['startDate'])) {
                $recurrence->setStartDate(new \DateTimeImmutable($recurrenceData['startDate']));
            }
            if (isset($recurrenceData['endDate'])) {
                $recurrence->setEndDate(new \DateTimeImmutable($recurrenceData['endDate']));
            }

            $movement->setRecurrence($recurrence);
        } else {
            $movement->setRecurrence(null);
        }
    }
}
