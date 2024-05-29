<?php

namespace Tests\StringPhp\Models;

use Tests\StringPhp\Models\Models\User;

$user = null;
$userData = [
    'id' => faker()->numberBetween(1, 100),
    'name' => faker()->firstName,
    'email' => faker()->email,
    'password' => faker()->password,
];

it('should create user model from data', function () use (&$user, &$userData) {
    $user = User::map($userData);

    expect($user->id)->toBe($userData['id']);
});

it('should serialize back to an array', function () use (&$user, &$userData) {
    expect($user->arraySerialize())->toBe($userData);
});

it('should serialize to an array and remap back to a model', function () use (&$user) {
    $data = $user->arraySerialize();

    // Should not throw an exception
    $remappedUser = User::map($data);

    expect($user->id)->toBe($remappedUser->id);
});
