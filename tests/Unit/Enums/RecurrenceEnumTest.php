<?php

namespace App\Tests\Unit\Enums;

use App\Enums\RecurrenceEnum;
use PHPUnit\Framework\TestCase;

class RecurrenceEnumTest extends TestCase
{
    public function testDaily(): void
    {
        $this->assertEquals('daily', RecurrenceEnum::Daily->value);
        $this->assertEquals('Daily', RecurrenceEnum::Daily->name);
    }

    public function testWeekly(): void
    {
        $this->assertEquals('weekly', RecurrenceEnum::Weekly->value);
        $this->assertEquals('Weekly', RecurrenceEnum::Weekly->name);
    }

    public function testBimonthly(): void
    {
        $this->assertEquals('bimonthly', RecurrenceEnum::Bimonthly->value);
        $this->assertEquals('Bimonthly', RecurrenceEnum::Bimonthly->name);
    }

    public function testQuarterly(): void
    {
        $this->assertEquals('quarterly', RecurrenceEnum::Quarterly->value);
        $this->assertEquals('Quarterly', RecurrenceEnum::Quarterly->name);
    }

    public function testMonthly(): void
    {
        $this->assertEquals('monthly', RecurrenceEnum::Monthly->value);
        $this->assertEquals('Monthly', RecurrenceEnum::Monthly->name);
    }

    public function testYearly(): void
    {
        $this->assertEquals('yearly', RecurrenceEnum::Yearly->value);
        $this->assertEquals('Yearly', RecurrenceEnum::Yearly->name);
    }
}
