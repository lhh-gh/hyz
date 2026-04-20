<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\DealCardService;
use App\Exception\BusinessException;
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Support\DistributedLocker;
use Hyperf\Contract\ConfigInterface;

final class StartGameService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly DealCardService $dealCardService,
        private readonly DistributedLocker $locker,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(string $roomId): Room
    {
        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        return $this->locker->withLock(RedisKey::lockRoom($roomId), function () use ($roomId) {
            $room = $this->roomRepository->find($roomId);
            if ($room === null) {
                throw new BusinessException('Room does not exist', 4303);
            }

            $room = $this->dealCardService->execute($room);
            $this->roomRepository->save($room);

            return $room;
        }, $lockTtl);
    }
}
