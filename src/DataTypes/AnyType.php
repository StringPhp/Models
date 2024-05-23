<?php

namespace StringPhp\Models\DataTypes;

use Override;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AnyType extends DataType
{
    public function __construct(
        bool $required = true
    ) {
        parent::__construct($required);
    }

    #[Override]
    public function isType(mixed $value): bool
    {
        return true;
    }

    #[Override]
    public function isRequired(): bool
    {
        return $this->required;
    }
}
