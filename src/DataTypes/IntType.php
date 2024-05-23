<?php

namespace StringPhp\Models\DataTypes;

class IntType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::INT, $required);
    }
}