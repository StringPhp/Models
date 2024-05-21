<?php

namespace StringPhp\Models\DataTypes;

use Override;

class AnyType extends DataType
{
    public function __construct(
        public readonly bool $required = true
    ) {

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
