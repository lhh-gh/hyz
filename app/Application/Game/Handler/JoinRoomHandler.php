<?php
declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Gateway\WsHandlerInterface;
use App\Application\Room\JoinRoomService;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class JoinRoomHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly JoinRoomService $service,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $roomId = (string) ($message->data['room_id'] ?? '');
        $room = $this->service->execute((string) $message->account, $roomId);

        $this->pusher->push(
            $server,
            $message->fd,
            MainCmd::CMD_SYS,
            SubCmd::ENTER_ROOM_SUCC_RESP,
            [
                'status' => 'success',
                'room_id' => $room->roomId,
                'players' => array_keys($room->players),
            ]
        );
    }
}