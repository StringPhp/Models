<?php

namespace StringPhp\Models\DataTypes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NativeType extends DataType
{
    public const int STRING = 0;
    public const int INT = 1;
    public const int FLOAT = 2;
    public const int BOOL = 3;
    public const int NULL = 4;

    public function __construct(
        public readonly int $type,
        bool $required = true
    ) {
        parent::__construct($required);
    }

    public function isType(mixed $value): bool
    {
        return match ($this->type) {
            self::STRING => is_string($value),
            self::INT => is_int($value),
            self::FLOAT => is_float($value),
            self::BOOL => is_bool($value),
            self::NULL => $value === null,
            default => false,
        };
    }
}
