<?php
namespace App\Service;

use App\Entity\Recurrence;
use App\Enums\RecurrenceEnum;
use Carbon\CarbonImmutable;

class RecurrenceService
{
    public function createRecurrence($movement, $data)
    {
        if (isset($data['recurrence']) && RecurrenceEnum::from($data['recurrence']['name'])) {
            $recurrenceData = $data['recurrence'];
            $recurrence = new Recurrence();
            $recurrence->setName($recurrenceData['name']);

            // Convertit les dates de chaîne en objets CarbonImmutable
            if (isset($recurrenceData['startDate'])) {
                $recurrence->setStartDate(CarbonImmutable::createFromFormat('d/m/Y', $recurrenceData['startDate']));
            }
            if (isset($recurrenceData['endDate'])) {
                $recurrence->setEndDate(CarbonImmutable::createFromFormat('d/m/Y', $recurrenceData['endDate']));
            }

            $movement->setRecurrence($recurrence);
        } else {
            $movement->setRecurrence(null);
        }
    }
}
