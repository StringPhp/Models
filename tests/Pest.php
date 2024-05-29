<?php

namespace Tests\StringPhp\Models;

use Faker\Factory;

expect()->extend('toBeSnakeCaseArray', function () {
    if (!is_array($this->value)) {
        return false;
    }

    foreach ($this->value as $key => $value) {
        expect($key)->toBeString()
            ->and($key)->toMatch('/^[a-z0-9_]+$/');
    }

    return true;
});

function faker(): \Faker\Generator
{
    static $faker;

    if (!$faker) {
        $faker = Factory::create();
    }

    return $faker;
}
