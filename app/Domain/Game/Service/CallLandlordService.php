<?php

declare(strict_types=1);

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Enum\RoomStatus;
use App\Exception\BusinessException;

final class CallLandlordService
{
    public function execute(Room $room, string $account, int $action): Room
    {
        if ($room->status !== RoomStatus::Calling) {
            throw new BusinessException('当前阶段不能叫地主', 4202);
        }

        $player = $room->getPlayer($account);
        if ($player->chairId !== $room->currentChairId) {
            throw new BusinessException('未轮到当前玩家叫地主', 4203);
        }

        $player->callAction = $action;

        $allFinished = true;
        $called = [];
        foreach ($room->players as $item) {
            if ($item->callAction === null) {
                $allFinished = false;
            }
            if ($item->callAction === 1) {
                $called[] = $item;
            }
        }

        if (! $allFinished) {
            $room->currentChairId = $room->currentChairId >= 3 ? 1 : $room->currentChairId + 1;
            return $room;
        }

        $winner = $called !== [] ? $called[0] : current($room->players);
        $winner->isLandlord = true;
        $winner->cards = array_values(array_merge($winner->cards, $room->baseCards));
        $room->landlord = $winner->account;
        $room->status = RoomStatus::Playing;
        $room->currentChairId = $winner->chairId;

        return $room;
    }
}