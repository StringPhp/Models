<?php

namespace StringPhp\Models\DataTypes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType extends DataType
{
    public function __construct(
        public readonly DataType $type,
        bool $required = true
    )
    {
        parent::__construct($required);
    }

    public function isType(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->type->isType($item)) {
                return false;
            }
        }

        return true;
    }

    public function beforeMap(mixed &$value): void
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as &$item) {
            $this->type->beforeMap($item);
        }
    }
}