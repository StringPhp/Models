<?php

namespace Tests\StringPhp\Models\Models;

use StringPhp\Models\DataTypes\ModelType;
use StringPhp\Models\JsonModel;

class NestedJsonModel extends JsonModel
{
    #[ModelType(User::class)]
    public User $adminUser;

    #[ModelType(Song::class)]
    public Song $song;
    public int $permissionLevel;
}