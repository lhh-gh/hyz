<?php

declare(strict_types=1);

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Enum\RoomStatus;
use App\Exception\BusinessException;
use App\Game\Core\DdzPoker;

final class DealCardService
{
    public function __construct(
        private readonly DdzPoker $ddzPoker,
    ) {
    }

    public function execute(Room $room): Room
    {
        if (count($room->players) !== 3) {
            throw new BusinessException('Not enough players to start the game', 4201);
        }

        $accounts = array_keys($room->players);
        $deal = $this->ddzPoker->dealCards($accounts);

        foreach ($accounts as $account) {
            $room->players[$account]->cards = $deal['card'][$account];
        }

        $room->baseCards = $deal['card']['hand'];
        $room->status = RoomStatus::Calling;
        $room->currentChairId = 1;

        return $room;
    }
}
