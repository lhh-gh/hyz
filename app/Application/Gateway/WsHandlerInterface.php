<?php

declare(strict_types=1);

namespace App\Application\Gateway;

use App\DTO\WsMessage;
use Swoole\WebSocket\Server;

/**
 *  Handler 接口
 */
interface WsHandlerInterface
{
    public function handle(Server $server, WsMessage $message): void;
}