<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\PlayCardService;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class PlayCardAppService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly PlayCardService $playCardService,
        private readonly ConnectionManager $connectionManager,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function execute(Server $server, string $account, array $cards, bool $pass): void
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('房间不存在', 4302);
        }

        $room = $this->playCardService->execute($room, $account, $cards, $pass);
        $this->roomRepository->save($room);

        foreach (array_keys($room->players) as $playerAccount) {
            $fd = $this->connectionManager->getFdByAccount($playerAccount);
            if ($fd === null) {
                continue;
            }

            $this->pusher->push(
                $server,
                $fd,
                MainCmd::CMD_GAME,
                SubCmd::SUB_GAME_OUT_CARD_RESP,
                [
                    'status' => 'success',
                    'room_id' => $room->roomId,
                    'current_chair_id' => $room->currentChairId,
                    'last_played_cards' => $room->lastPlayedCards,
                    'last_played_chair_id' => $room->lastPlayedChairId,
                    'game_status' => $room->status->value,
                ]
            );
        }
    }
}