<?php

namespace StringPhp\Models;

use BackedEnum;
use JsonSerializable;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionUnionType;
use StringPhp\Models\DataTypes\AnyType;
use StringPhp\Models\DataTypes\DataType;
use StringPhp\Models\DataTypes\NativeType;
use StringPhp\Models\Exception\InvalidValue;
use StringPhp\Models\Exception\MissingRequiredValue;

abstract class Model implements JsonSerializable
{
    public function jsonSerialize(bool $withSensitiveProperties = false): array
    {
        $sensitiveParams = (function (): array {
            $params = [];

            $reflection = new ReflectionClass($this);

            foreach ($reflection->getProperties() as $property) {
                $attributes = $property->getAttributes(SensitiveProperty::class, ReflectionAttribute::IS_INSTANCEOF);

                if (empty($attributes)) {
                    continue;
                }

                $params[] = $property->getName();
            }

            return $params;
        })();

        $vars = [];

        foreach (get_object_vars($this) as $key => $var) {
            if (!$withSensitiveProperties && in_array($key, $sensitiveParams)) {
                continue;
            }

            if ($var instanceof JsonSerializable) {
                $value = $var->jsonSerialize();
            } else if ($var instanceof BackedEnum) {
                $value = $var->value;
            } else {
                $value = $var;
            }

            $vars[$key] = $value;
        }

        return $vars;
    }

    public function fill(array $fields, array $allowedKeys = []): void
    {
        $serialized = $this->jsonSerialize(true);

        foreach ($fields as $key => $value) {
            if (
                !property_exists($this, $key) ||
                (!empty($allowedKeys) && !in_array($key, $allowedKeys))
            ) {
                continue;
            }

            $serialized[$key] = $value;
        }

        /** @var self $remapped */
        $remapped = static::map($serialized);

        foreach ($fields as $key => $value) {
            if (
                !property_exists($this, $key) ||
                (!empty($allowedKeys) && !in_array($key, $allowedKeys)) ||
                (!isset($remapped->{$key}) && $value !== null)
            ) {
                continue;
            }

            $this->{$key} = $remapped?->{$key} ?? $value;
        }
    }

    public static function map(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            /**
             * @var DataType[] $acceptedTypes
             */
            $acceptedTypes = array_map(
                static fn (ReflectionAttribute $type) => $type->newInstance(),
                $property->getAttributes(DataType::class, ReflectionAttribute::IS_INSTANCEOF)
            );

            $propertySet = isset($data[$propertyName]);
            $mappedProperty = false;

            if (empty($acceptedTypes)) {
                $dataType = $property->getType();

                if ($dataType instanceof ReflectionUnionType) {
                    $dataTypes = $dataType->getTypes();
                } elseif ($dataType !== null) {
                    $dataTypes = [$dataType];
                } else {
                    continue;
                }

                foreach ($dataTypes as $type) {
                    $nativeType = ['string', 'int', 'float', 'bool'];

                    if (in_array($type->getName(), $nativeType)) {
                        $acceptedTypes[] = new NativeType(
                            match ($type->getName()) {
                                'string' => NativeType::STRING,
                                'int' => NativeType::INT,
                                'float' => NativeType::FLOAT,
                                'bool' => NativeType::BOOL,
                                default => throw new LogicException('Invalid type')
                            }
                        );
                    } else {
                        $acceptedTypes[] = new AnyType();
                    }
                }
            }

            foreach ($acceptedTypes as $type) {
                if (
                    !$propertySet &&
                    $type->isRequired() &&
                    !$property->getType()->allowsNull()
                ) {
                    throw new MissingRequiredValue($instance::class, $propertyName);
                } elseif (!$propertySet) {
                    continue;
                }

                $value = $data[$propertyName];

                if ($type->isType($value)) {
                    $type->beforeMap($value);
                    $property->setValue($instance, $value);
                    $mappedProperty = true;
                    break;
                }
            }

            if (!$mappedProperty && $propertySet) {
                throw new InvalidValue($instance::class, $propertyName, $data[$propertyName] ?? null, $acceptedTypes);
            }
        }

        return $instance;
    }

    /**
     * @return array Values that have changed
     */
    public function compare(self $model): array
    {
        $changedFieldNames = [];

        foreach ((new ReflectionClass(static::class))->getProperties() as $property) {
            $propertyName = $property->getName();

            if (
                (isset($model->{$propertyName}) !== isset($this->{$propertyName})) ||
                (
                    isset($model->{$propertyName}) &&
                    isset($this->{$propertyName}) &&
                    ($model->{$propertyName} !== $this->{$propertyName})
                )
            ) {
                $changedFieldNames[] = $propertyName;
            }
        }

        return $changedFieldNames;
    }
}
