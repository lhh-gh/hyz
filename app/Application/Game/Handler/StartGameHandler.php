<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Game\StartGameService;
use App\Application\Gateway\WsHandlerInterface;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class StartGameHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly StartGameService $service,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $roomId = (string) ($message->data['room_id'] ?? '');
        $room = $this->service->execute($roomId);
        $player = $room->getPlayer((string) $message->account);

        $this->pusher->push(
            $server,
            $message->fd,
            MainCmd::CMD_GAME,
            SubCmd::SUB_GAME_START_RESP,
            [
                'status' => 'success',
                'code' => 0,
                'message' => 'game started',
                'data' => [
                    'room_id' => $room->roomId,
                    'room_status' => $room->status->value,
                    'current_chair_id' => $room->currentChairId,
                    'my_cards' => $player->cards,
                    'base_cards' => $room->baseCards,
                ],
            ],
        );
    }
}
