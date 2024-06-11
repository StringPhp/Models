<?php

/**
 * Regular data array with matching keys
 */

use Tests\StringPhp\Models\Models\NestedJsonModel;
use Tests\StringPhp\Models\Models\Song;

$rData = [
    'name' => 'Song Name',
    'artistId' => 1,
    'length' => 180,
];

/**
 * Data array with snake_case keys
 */
$sData = [
    'name' => 'Song Name',
    'artist_id' => 1,
    'length' => 180,
];

it('maps and serializes model all supported ways', function () use (&$rData, &$sData) {
    $rMapped = Song::map($rData);
    $sMapped = Song::mapFromJson($sData);

    expect($rMapped->name)->toBe($sMapped->name)
        ->and($rMapped->artistId)->toBe($sMapped->artistId)
        ->and($rMapped->length)->toBe($sMapped->length)
        ->and($sMapped->arraySerialize())->toBe($rData);

    $sReMapped = Song::mapFromJson($sMapped->jsonSerialize());

    expect($rMapped->name)->toBe($sReMapped->name)
        ->and($rMapped->artistId)->toBe($sReMapped->artistId)
        ->and($rMapped->length)->toBe($sReMapped->length)
        ->and($sReMapped->arraySerialize())->toBe($rData);
});

it('translates nested json model array keys', function () {
    $admin = [
        'admin_user' => [
            'id' => 1,
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => '123456'
        ],
        'song' => [
            'name' => 'Song Name',
            'artist_id' => 1,
            'length' => 180
        ],
        'permission_level' => 5
    ];

    $adminModel = NestedJsonModel::mapFromJson($admin);

    expect($adminModel->adminUser->id)->toBe(1)
        ->and($adminModel->adminUser->name)->toBe('admin')
        ->and($adminModel->adminUser->email)->toBe('admin@example.com')
        ->and($adminModel->adminUser->password)->toBe('123456')
        ->and($adminModel->permissionLevel)->toBe(5)
        ->and($adminModel->song->name)->toBe('Song Name')
        ->and($adminModel->song->artistId)->toBe(1)
        ->and($adminModel->song->length)->toBe(180);
});
