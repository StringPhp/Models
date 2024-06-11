<?php

namespace StringPhp\Models\DataTypes;

use Attribute;
use InvalidArgumentException;
use Override;
use StringPhp\Models\JsonModel;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ModelType extends DataType
{
    public function __construct(
        public readonly string $className,
        bool $required = true,
    ) {
        if (!class_exists($this->className)) {
            throw new InvalidArgumentException("Class {$this->className} does not exist.");
        }

        parent::__construct($required);
    }

    #[Override]
    public function isType(mixed $value): bool
    {
        return $value instanceof $this->className || is_array($value);
    }

    public function beforeMap(mixed &$value): void
    {
        if (in_array(JsonModel::class, class_parents($this->className)) && is_array($value)) {
            $value = [$this->className, 'mapFromJson']($value);
        } else if (is_array($value)) {
            $value = [$this->className, 'map']($value);
        }
    }
}
