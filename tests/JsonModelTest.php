<?php

/**
 * Regular data array with matching keys
 */

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