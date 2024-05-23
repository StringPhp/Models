<?php

namespace StringPhp\Models\DataTypes;

class NullType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::NULL, $required);
    }
}