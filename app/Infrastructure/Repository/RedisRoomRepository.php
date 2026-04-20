<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Persistence\Redis\RedisRoomSerializer;
use Hyperf\Redis\Redis;

final class RedisRoomRepository implements RoomRepositoryInterface
{
    public function __construct(
        private readonly Redis $redis,
        private readonly RedisRoomSerializer $serializer,
    ) {
    }

    public function save(Room $room): void
    {
        $this->redis->set(RedisKey::room($room->roomId), $this->serializer->serialize($room));

        foreach (array_keys($room->players) as $account) {
            $this->bindAccountToRoom((string) $account, $room->roomId);
        }
    }

    public function find(string $roomId): ?Room
    {
        $payload = $this->redis->get(RedisKey::room($roomId));
        if (! is_string($payload) || $payload === '') {
            return null;
        }

        return $this->serializer->deserialize($payload);
    }

    public function findByAccount(string $account): ?Room
    {
        $roomId = $this->redis->get(RedisKey::roomPlayerIndex($account));
        if (! is_string($roomId) || $roomId === '') {
            return null;
        }

        return $this->find($roomId);
    }

    public function bindAccountToRoom(string $account, string $roomId): void
    {
        $this->redis->set(RedisKey::roomPlayerIndex($account), $roomId);
    }

    public function removeAccountRoomBinding(string $account): void
    {
        $this->redis->del(RedisKey::roomPlayerIndex($account));
    }
}