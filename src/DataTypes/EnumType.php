<?php

namespace StringPhp\Models\DataTypes;

use Attribute;
use BackedEnum;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EnumType extends DataType
{
    /** @var class-string<BackedEnum> */
    public readonly string $enum;

    /**
     * @param class-string<BackedEnum> $enum
     */
    public function __construct(
        string $enum,
        bool $required = true
    ) {
        $this->enum = $enum;

        if (!class_exists($this->enum) || !is_subclass_of($this->enum, BackedEnum::class)) {
            throw new InvalidArgumentException('EnumType must be an instance of BackedEnum and must exist.');
        }

        parent::__construct($required);
    }

    public function isType(mixed $value): bool
    {
        if ($value instanceof $this->enum || [$this->enum, 'tryFrom']($value) !== null) {
            return true;
        }

        return false;
    }

    public function beforeMap(mixed &$value): void
    {
        if ($value instanceof $this->enum) {
            return;
        }

        $value = [$this->enum, 'tryFrom']($value);
    }
}
