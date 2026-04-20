<?php

declare(strict_types=1);

namespace App\Application\Room;

use App\Domain\Game\Enum\RoomStatus;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Support\DistributedLocker;
use Hyperf\Contract\ConfigInterface;

final class DisconnectService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly DistributedLocker $locker,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(string $account): void
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            return;
        }

        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        $this->locker->withLock(RedisKey::lockRoom($room->roomId), function () use ($account) {
            $room = $this->roomRepository->findByAccount($account);
            if ($room === null) {
                return;
            }

            match ($room->status) {
                RoomStatus::Waiting, RoomStatus::Ready => $this->handleLobbyDisconnect($room, $account),
                RoomStatus::Calling, RoomStatus::Playing => $this->handleInGameDisconnect($account),
                RoomStatus::Finished => $this->handleFinishedDisconnect($account),
            };
        }, $lockTtl);
    }

    private function handleLobbyDisconnect(\App\Domain\Game\Entity\Room $room, string $account): void
    {
        $this->roomRepository->removeAccountRoomBinding($account);
        unset($room->players[$account]);

        if ($room->players === []) {
            $this->roomRepository->delete($room->roomId);
            return;
        }

        $room->status = RoomStatus::Waiting;
        $this->roomRepository->save($room);
    }

    private function handleInGameDisconnect(string $account): void
    {
        $this->roomRepository->removeAccountRoomBinding($account);
    }

    private function handleFinishedDisconnect(string $account): void
    {
        $this->roomRepository->removeAccountRoomBinding($account);
    }
}