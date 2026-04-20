<?php

declare(strict_types=1);

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Room;

final class SettlementService
{
    public function build(Room $room, string $winnerAccount): array
    {
        $landlord = $room->landlord;
        $landlordWon = $landlord === $winnerAccount;
        $result = [];

        foreach ($room->players as $player) {
            $isWinner = $player->account === $winnerAccount;
            $score = $player->account === $landlord
                ? ($landlordWon ? 2 : -2)
                : ($landlordWon ? -1 : 1);

            $result[] = [
                'account' => $player->account,
                'win' => $isWinner,
                'score' => $score,
                'is_landlord' => $player->account === $landlord,
            ];
        }

        return [
            'winner' => $winnerAccount,
            'landlord' => $landlord,
            'room_status' => $room->status->value,
            'result' => $result,
        ];
    }
}
