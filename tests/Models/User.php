<?php

namespace Tests\StringPhp\Models\Models;

use StringPhp\Models\Model;

class User extends Model
{
    public int $id;
    public string $name;
    public string $email;
    public string $password;
}