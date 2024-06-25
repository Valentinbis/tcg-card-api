<?php

namespace App\Tests\Unit\Enums;

use App\Enums\MovementEnum;
use PHPUnit\Framework\TestCase;

class MovementEnumTest extends TestCase
{
    public function testExpense(): void
    {
        $this->assertEquals('expense', MovementEnum::Expense->value);
        $this->assertEquals('Expense', MovementEnum::Expense->name);
    }

    public function testIncome(): void
    {
        $this->assertEquals('income', MovementEnum::Income->value);
        $this->assertEquals('Income', MovementEnum::Income->name);
    }
}
