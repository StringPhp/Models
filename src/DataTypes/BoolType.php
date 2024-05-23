<?php

namespace StringPhp\Models\DataTypes;

class BoolType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::BOOL, $required);
    }
}