<?php

namespace StringPhp\Models;

use BackedEnum;
use JsonSerializable;
use LogicException;
use StringPhp\Models\Exception\InvalidValue;
use StringPhp\Models\Exception\MissingRequiredValue;

use StringPhp\Models\Exception\ModelException;
use function StringPhp\Utils\camelToSnakeCase;
use function StringPhp\Utils\snakeToCamelCase;

class JsonModel extends Model implements JsonSerializable
{
    /**
     * Converts the model to a snake_case JSON serializable array.
     *
     * @param bool $withSensitiveProperties
     * @return array
     */
    public function jsonSerialize(bool $withSensitiveProperties = false): array
    {
        $sensitiveParams = $this->getSensitiveParams();
        $vars = [];

        foreach ($this->getVars() as $key => $var) {
            $scKey = camelToSnakeCase($key);

            if (!$withSensitiveProperties && in_array($key, $sensitiveParams)) {
                continue;
            }

            if ($var instanceof Model && !class_parents($var, self::class)) {
                throw new LogicException('Nested models should extend JsonModel if they are to be serialized');
            } else if ($var instanceof self) {
                $value = $var->jsonSerialize($withSensitiveProperties);
            } else if ($var instanceof JsonSerializable) {
                $value = $var->jsonSerialize();
            } else if ($var instanceof BackedEnum) {
                $value = $var->value;
            } else {
                $value = $var;
            }

            $vars[$scKey] = $value;
        }

        return $vars;
    }

    /**
     * Maps model from a snake_case JSON array.
     *
     * @param array $data
     * @return self
     *
     * @throws InvalidValue|MissingRequiredValue|ModelException
     */
    public static function mapFromJson(array $data): static
    {
        foreach ($data as $key => $value) {
            $ccKey = snakeToCamelCase($key);

            if (property_exists(static::class, $ccKey)) {
                unset($data[$key]);
                $data[$ccKey] = $value;
            }
        }

        return parent::map($data);
    }
}