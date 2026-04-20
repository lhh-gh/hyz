<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Gateway\WsHandlerInterface;
use App\Application\Room\CreateRoomService;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class CreateRoomHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly CreateRoomService $service,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $room = $this->service->execute((string) $message->account);

        $this->pusher->push(
            $server,
            $message->fd,
            MainCmd::CMD_SYS,
            SubCmd::CREATE_ROOM_SUCC_RESP,
            [
                'status' => 'success',
                'room_id' => $room->roomId,
            ]
        );
    }
}