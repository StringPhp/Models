<?php

namespace StringPhp\Models\Exception;

use StringPhp\Models\DataTypes\DataType;
use StringPhp\Models\Model;

class InvalidValue extends ModelException
{
    /**
     * @param class-string<Model> $model
     * @param DataType[] $acceptedTypes
     */
    public function __construct(
        public readonly string $model,
        public readonly string $property,
        public readonly mixed $value,
        public readonly array $acceptedTypes
    ) {
        parent::__construct(
            sprintf(
                'Invalid value for property %s in model %s: %s',
                $property,
                $model,
                print_r($value, true)
            )
        );
    }
}
