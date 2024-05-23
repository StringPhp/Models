<?php

namespace StringPhp\Models\DataTypes;

class StringType extends NativeType
{
    public function __construct(bool $required = true)
    {
        parent::__construct(NativeType::STRING, $required);
    }
}