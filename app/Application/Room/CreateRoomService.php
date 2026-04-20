<?php

declare(strict_types=1);

namespace App\Application\Room;

use App\Domain\Game\Entity\Player;
use App\Domain\Game\Entity\Room;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Exception\BusinessException;
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Support\DistributedLocker;
use Hyperf\Contract\ConfigInterface;

final class CreateRoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly ConfigInterface $config,
        private readonly DistributedLocker $locker,
    ) {
    }

    public function execute(string $account): Room
    {
        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        return $this->locker->withLock(RedisKey::lockAccount($account), function () use ($account) {
            if ($this->roomRepository->findByAccount($account) !== null) {
                throw new BusinessException('Player is already in a room', 4101);
            }

            $min = (int) $this->config->get('game.room.room_id_min', 100001);
            $max = (int) $this->config->get('game.room.room_id_max', 999999);
            $retryMax = (int) $this->config->get('game.lock.retry_max', 10);

            $roomId = null;
            for ($i = 0; $i < $retryMax; $i++) {
                $candidate = (string) random_int($min, $max);
                if ($this->roomRepository->find($candidate) === null) {
                    $roomId = $candidate;
                    break;
                }
            }

            if ($roomId === null) {
                throw new BusinessException('Unable to generate unique room ID, please retry', 4104);
            }

            $room = new Room(roomId: $roomId);
            $room->addPlayer(new Player($account, 1));
            $this->roomRepository->save($room);

            return $room;
        }, $lockTtl);
    }
}
