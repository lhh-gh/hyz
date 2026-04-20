<?php

declare(strict_types=1);

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Enum\RoomStatus;
use App\Exception\BusinessException;
use App\Game\Core\DdzPoker;

final class PlayCardService
{
    public function __construct(
        private readonly DdzPoker $ddzPoker,
    ) {
    }

    public function execute(Room $room, string $account, array $cards, bool $pass): Room
    {
        if ($room->status !== RoomStatus::Playing) {
            throw new BusinessException('当前阶段不能出牌', 4204);
        }

        $player = $room->getPlayer($account);
        if ($player->chairId !== $room->currentChairId) {
            throw new BusinessException('未轮到当前玩家出牌', 4205);
        }

        if ($pass) {
            if ($room->lastPlayedChairId === null || $room->lastPlayedChairId === $player->chairId) {
                throw new BusinessException('首轮不能过牌', 4206);
            }

            $room->steps[] = [
                'account' => $account,
                'chair_id' => $player->chairId,
                'action' => 'pass',
            ];
            $room->currentChairId = $player->chairId >= 3 ? 1 : $player->chairId + 1;
            return $room;
        }

        if ($cards === []) {
            throw new BusinessException('出牌不能为空', 4207);
        }

        if (array_diff($cards, $player->cards) !== []) {
            throw new BusinessException('出牌不在手牌中', 4208);
        }

        $type = $this->ddzPoker->checkCardType($cards);
        if (($type['type'] ?? 0) === 0) {
            throw new BusinessException('牌型错误', 4209);
        }

        if ($room->lastPlayedCards !== null && ! $this->ddzPoker->checkCardSize($cards, $room->lastPlayedCards)) {
            throw new BusinessException('牌没有大过上家', 4210);
        }

        $player->cards = array_values(array_diff($player->cards, $cards));
        $player->playedCards = array_values(array_merge($player->playedCards, $cards));

        $room->lastPlayedCards = $cards;
        $room->lastPlayedChairId = $player->chairId;
        $room->currentChairId = $player->chairId >= 3 ? 1 : $player->chairId + 1;
        $room->steps[] = [
            'account' => $account,
            'chair_id' => $player->chairId,
            'action' => 'play',
            'cards' => $cards,
            'left' => count($player->cards),
        ];

        if ($player->cards === []) {
            $room->status = RoomStatus::Finished;
        }

        return $room;
    }
}