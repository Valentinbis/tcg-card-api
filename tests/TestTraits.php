<?php

namespace App\Tests;

trait TestTraits {
    protected function setPrivateProperty($object, string $propertyName, $value): void {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}