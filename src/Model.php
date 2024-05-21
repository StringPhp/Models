<?php

namespace StringPhp\Models;

use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionUnionType;
use StringPhp\Models\DataTypes\AnyType;
use StringPhp\Models\DataTypes\DataType;
use StringPhp\Models\DataTypes\NativeType;
use StringPhp\Models\Exception\InvalidValue;
use StringPhp\Models\Exception\MissingRequiredValue;

abstract class Model
{
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
}
