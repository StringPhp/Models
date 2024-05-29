<?php

use StringPhp\Models\Exception\InvalidValue;
use StringPhp\Models\Exception\MissingRequiredValue;
use Tests\StringPhp\Models\Models\DataTypes;
use Tests\StringPhp\Models\Models\TestEnum;

use function Tests\StringPhp\Models\faker;

it('maps all data types', function () {
    $data = [
        'string' => faker()->sentence(),
        'int' => faker()->numberBetween(1, 100),
        'float' => faker()->randomFloat(2, 1, 100),
        'bool' => faker()->boolean(),
        'array' => [faker()->word(), faker()->word()],
        'mixed' => faker()->word(),
        'null' => null,
        'enum' => TestEnum::ONE,
    ];

    $model = DataTypes::map($data);

    expect($model->string)->toBe($data['string'])
        ->and($model->int)->toBe($data['int'])
        ->and($model->float)->toBe($data['float'])
        ->and($model->bool)->toBe($data['bool'])
        ->and($model->array)->toBe($data['array'])
        ->and($model->mixed)->toBe($data['mixed'])
        ->and($model->null)->toBe($data['null'])
        ->and($model->enum)->toBe($data['enum']);
});

it('does not map invalid types', function () {
    $data = [
        'string' => faker()->sentence(),
        'int' => faker()->numberBetween(1, 100),
        'float' => faker()->randomFloat(2, 1, 100),
        'bool' => faker()->boolean(),
        'array' => [faker()->word(), faker()->word()],
        'mixed' => faker()->word(),
        'null' => null,
        'enum' => 'invalid',
    ];

    $this->expectException(InvalidValue::class);

    $model = DataTypes::map($data);
});

it('does not map when required value is missing', function () {
    $data = [
        'string' => faker()->sentence(),
        'int' => faker()->numberBetween(1, 100),
        'float' => faker()->randomFloat(2, 1, 100),
        'bool' => faker()->boolean(),
        'array' => [faker()->word(), faker()->word()],
        'mixed' => faker()->word(),
        'null' => null,
    ];

    $this->expectException(MissingRequiredValue::class);

    $model = DataTypes::map($data);
});
