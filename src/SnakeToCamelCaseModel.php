<?php

namespace StringPhp\Models;

use function StringPhp\Utils\camelToSnakeCase;
use function StringPhp\Utils\snakeToCamelCase;

abstract class SnakeToCamelCaseModel extends Model
{
    public function fill(array $fields, array $allowedKeys = []): void
    {
        foreach ($fields as $key => $value) {
            $ccKey = snakeToCamelCase($key);

            if (property_exists(static::class, $ccKey)) {
                unset($fields[$key]);
                $fields[$ccKey] = $value;
                continue;
            }
        }

        parent::fill($fields, $allowedKeys);
    }

    public static function map(array $data): static
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

    public function jsonSerialize(bool $withSensitiveProperties = false): array
    {
        $data = parent::jsonSerialize($withSensitiveProperties);

        foreach ($data as $key => $value) {
            $scKey = camelToSnakeCase($key);

            if ($scKey !== $key) {
                unset($data[$key]);
                $data[$scKey] = $value;
            }
        }

        return $data;
    }
}