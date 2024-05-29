<?php

namespace StringPhp\Models;

use BackedEnum;
use JsonSerializable;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionUnionType;
use StringPhp\Models\DataTypes\AnyType;
use StringPhp\Models\DataTypes\ArrayType;
use StringPhp\Models\DataTypes\DataType;
use StringPhp\Models\DataTypes\EnumType;
use StringPhp\Models\DataTypes\NativeType;
use StringPhp\Models\Exception\InvalidValue;
use StringPhp\Models\Exception\MissingRequiredValue;
use StringPhp\Models\Exception\ModelException;

abstract class Model
{
    public function getSensitiveParams(): array
    {
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
    }

    /**
     * Serializes the object to an array that can be used for JSON serialization
     *
     * @param bool $withSensitiveProperties
     * @return array
     */
    public function arraySerialize(bool $withSensitiveProperties = false): array
    {
        $sensitiveParams = $this->getSensitiveParams();
        $vars = [];

        foreach (get_object_vars($this) as $key => $var) {
            if (!$withSensitiveProperties && in_array($key, $sensitiveParams)) {
                continue;
            }

            if ($var instanceof static) {
                $value = $var->arraySerialize($withSensitiveProperties);
            } else if ($var instanceof JsonSerializable) {
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

    /**
     * Maps an array to a model
     *
     * @param array $data
     * @return static
     *
     * @throws InvalidValue|MissingRequiredValue|ModelException
     */
    public static function map(array $data): static
    {
        $reflection = new ReflectionClass(static::class);

        try {
            $instance = $reflection->newInstanceWithoutConstructor();
        } catch (ReflectionException $e) {
            throw new ModelException('Failed to map model ' . static::class, previous: $e);
        }

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
                    } else if (is_subclass_of($type->getName(), BackedEnum::class)) {
                        $acceptedTypes[] = new EnumType($type->getName());
                    } else if ($type->getName() === 'array') {
                        $acceptedTypes[] = new ArrayType(new AnyType());
                    } else {
                        $acceptedTypes[] = new AnyType();
                    }
                }
            }

            $propertySet = array_key_exists($propertyName, $data);
            $mappedProperty = false;

            foreach ($acceptedTypes as $type) {
                if (
                    !$propertySet &&
                    $type->isRequired()
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
