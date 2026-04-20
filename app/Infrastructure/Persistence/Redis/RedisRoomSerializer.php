<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Redis;

use App\Domain\Game\Entity\Room;

/***
 *
 * 房间对象统一序列化
 */
final class RedisRoomSerializer
{
    public function serialize(Room $room): string
    {
        return json_encode($room->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $payload): Room
    {
        return Room::fromArray(
            json_decode($payload, true, 512, JSON_THROW_ON_ERROR)
        );
    }
}