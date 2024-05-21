<?php

namespace StringPhp\Models\DataTypes;

abstract class DataType
{
    abstract public function isType(mixed $value): bool;

    abstract public function isRequired(): bool;

    public function beforeMap(mixed &$value): void
    {

    }
}
