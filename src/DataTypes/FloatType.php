<?php

namespace StringPhp\Models\DataTypes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FloatType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::FLOAT, $required);
    }
}
