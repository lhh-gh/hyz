<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;

final class RoomSnapshotService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly ConnectionManager $connectionManager,
    ) {
    }

    public function execute(string $account): array
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4304);
        }

        $currentPlayer = $room->getPlayer($account);

        return [
            'room_id' => $room->roomId,
            'room_status' => $room->status->value,
            'landlord' => $room->landlord,
            'current_chair_id' => $room->currentChairId,
            'last_played_chair_id' => $room->lastPlayedChairId,
            'last_played_cards' => $room->lastPlayedCards,
            'my_cards' => $currentPlayer->cards,
            'players' => array_map(
                fn ($player) => [
                    'account' => $player->account,
                    'chair_id' => $player->chairId,
                    'card_count' => count($player->cards),
                    'is_landlord' => $player->isLandlord,
                    'online' => $this->connectionManager->isOnline($player->account),
                ],
                array_values($room->players),
            ),
        ];
    }
}
