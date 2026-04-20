<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Game\RoomSnapshotService;
use App\Application\Gateway\WsHandlerInterface;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class RoomSnapshotHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly RoomSnapshotService $service,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $snapshot = $this->service->execute((string) $message->account);

        $this->pusher->push(
            $server,
            $message->fd,
            MainCmd::CMD_GAME,
            SubCmd::ROOM_SNAPSHOT_RESP,
            [
                'status' => 'success',
                'code' => 0,
                'message' => 'room snapshot',
                'data' => $snapshot,
            ],
        );
    }
}
