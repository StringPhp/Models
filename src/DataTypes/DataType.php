<?php

namespace StringPhp\Models\DataTypes;

abstract class DataType
{
    public function __construct(
        public readonly bool $required = true
    ) {

    }

    abstract public function isType(mixed $value): bool;

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Do any final serialization before mapping
     */
    public function beforeMap(mixed &$value): void
    {

    }
}
