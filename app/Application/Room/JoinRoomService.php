<?php

declare(strict_types=1);

namespace App\Application\Room;

namespace App\Application\Room;

use App\Domain\Game\Entity\Player;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Exception\BusinessException;

final class JoinRoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
    ) {
    }

    public function execute(string $account, string $roomId)
    {
        if ($this->roomRepository->findByAccount($account) !== null) {
            throw new BusinessException('玩家已在其他房间中', 4102);
        }

        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw new BusinessException('房间不存在', 4103);
        }

        $room->addPlayer(new Player($account, count($room->players) + 1));
        $this->roomRepository->save($room);

        return $room;
    }
}