<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Gateway\WsHandlerInterface;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class HeartbeatHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $this->pusher->push(
            $server,
            $message->fd,
            MainCmd::CMD_SYS,
            SubCmd::HEARTBEAT_RESP,
            [
                'status' => 'success',
                'code' => 0,
                'message' => 'pong',
                'data' => [
                    'server_time' => time(),
                ],
            ],
        );
    }
}
