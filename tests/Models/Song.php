<?php

namespace Tests\StringPhp\Models\Models;

use StringPhp\Models\JsonModel;

class Song extends JsonModel
{
    public string $name;
    public int $artistId;
    public int $length;
}