<?php

declare(strict_types=1);

namespace App\Support;

use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class WsExceptionResponder
{
    public function __construct(
        private readonly MessagePusher $pusher,
    ) {
    }

    public function respond(Server $server, int $fd, \Throwable $throwable): void
    {
        $code = $throwable instanceof BusinessException ? $throwable->bizCode() : 5000;
        $message = $throwable instanceof BusinessException ? $throwable->getMessage() : 'Internal server error';

        $this->pusher->push(
            $server,
            $fd,
            MainCmd::CMD_SYS,
            SubCmd::SYSTEM_ERROR_RESP,
            [
                'status' => 'fail',
                'code' => $code,
                'message' => $message,
                'data' => [],
            ],
        );
    }
}
