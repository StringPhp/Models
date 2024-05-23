<?php

namespace StringPhp\Models\DataTypes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NullType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::NULL, $required);
    }
}