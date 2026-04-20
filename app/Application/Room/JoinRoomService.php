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

final class JoinRoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly DistributedLocker $locker,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(string $account, string $roomId): Room
    {
        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        return $this->locker->withLock(RedisKey::lockRoom($roomId), function () use ($account, $roomId) {
            if ($this->roomRepository->findByAccount($account) !== null) {
                throw new BusinessException('Player is already in another room', 4102);
            }

            $room = $this->roomRepository->find($roomId);
            if ($room === null) {
                throw new BusinessException('Room does not exist', 4103);
            }

            $room->addPlayer(new Player($account, count($room->players) + 1));
            $this->roomRepository->save($room);

            return $room;
        }, $lockTtl);
    }
}
