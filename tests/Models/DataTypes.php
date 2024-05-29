<?php

namespace Tests\StringPhp\Models\Models;

use StringPhp\Models\Model;

class DataTypes extends Model
{
    public string $string;
    public int $int;
    public float $float;
    public bool $bool;
    public array $array;
    public mixed $mixed;
    public null $null;
    public TestEnum $enum;
}