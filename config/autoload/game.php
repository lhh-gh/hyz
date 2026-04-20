<?php
declare(strict_types=1);

return [
    'room' => [
        'max_players' => 3,
        'room_id_min' => 100001,
        'room_id_max' => 999999,
        'snapshot_ttl' => 86400,
    ],
    'lock' => [
        'ttl' => 5,
        'retry_max' => 10,
    ],
    'matchmaking' => [
        'queue_key' => 'ddz:matchmaking:queue',
    ],
    'connection' => [
        'ttl' => 86400,
    ],
];