<?php

namespace StringPhp\Models\DataTypes;

class FloatType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::FLOAT, $required);
    }
}