<?php

declare(strict_types=1);

namespace App\Gateway\WebSocket;

use Swoole\WebSocket\Server;

final class MessagePusher
{
    public function __construct(
        private readonly PacketCodec $codec,
    ) {
    }

    public function push(Server $server, int $fd, int $cmd, int $scmd, array $data): void
    {
        if (! $server->isEstablished($fd)) {
            return;
        }

        $server->push($fd, $this->codec->encode($cmd, $scmd, $data), WEBSOCKET_OPCODE_BINARY);
    }

    /**
     * @param int[] $fds
     */
    public function pushToMany(Server $server, array $fds, int $cmd, int $scmd, array $data): void
    {
        foreach (array_unique($fds) as $fd) {
            if (is_int($fd) && $fd > 0) {
                $this->push($server, $fd, $cmd, $scmd, $data);
            }
        }
    }
}
