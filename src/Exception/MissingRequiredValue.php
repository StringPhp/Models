<?php

namespace StringPhp\Models\Exception;

use StringPhp\Models\Model;

class MissingRequiredValue extends ModelException
{
    /**
     * @param class-string<Model> $model
     */
    public function __construct(
        string $model,
        string $property
    ) {
        parent::__construct(
            sprintf(
                '%s in model %s is required.',
                $property,
                $model
            )
        );
    }
}
