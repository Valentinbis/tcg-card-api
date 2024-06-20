<?php

namespace App\Tests\Enums;

use App\Enums\RecurrenceEnum;
use PHPUnit\Framework\TestCase;

class RecurrenceEnumTest extends TestCase
{
    public function testDaily(): void
    {
        $this->assertEquals('daily', RecurrenceEnum::Daily->value);
        $this->assertEquals('Daily', RecurrenceEnum::Daily->name);
    }   
}
