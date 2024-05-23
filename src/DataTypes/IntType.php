<?php

namespace StringPhp\Models\DataTypes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::INT, $required);
    }
}