<?php

declare(strict_types=1);

namespace App\Application\Room;

use App\Domain\Game\Entity\Player;
use App\Domain\Game\Entity\Room;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Exception\BusinessException;
use Hyperf\Contract\ConfigInterface;

final class CreateRoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(string $account): Room
    {
        if ($this->roomRepository->findByAccount($account) !== null) {
            throw new BusinessException('Player is already in a room', 4101);
        }

        $room = new Room(
            roomId: (string) random_int(
                (int) $this->config->get('game.room.room_id_min', 100001),
                (int) $this->config->get('game.room.room_id_max', 999999),
            ),
        );

        $room->addPlayer(new Player($account, 1));
        $this->roomRepository->save($room);

        return $room;
    }
}
