<?php

declare(strict_types=1);

namespace App\Gateway\WebSocket;

use App\DTO\WsMessage;
use App\Exception\InvalidPacketException;

final class PacketCodec
{
    public function decode(string $payload, int $fd, ?string $account): WsMessage
    {
        $packet = \App\Game\Core\Packet::packDecode($payload);

        if (($packet['code'] ?? -1) !== 0) {
            throw new InvalidPacketException((string) ($packet['msg'] ?? '协议解码失败'));
        }

        $data = $packet['data'] ?? [];
        if ($data instanceof \stdClass) {
            $data = json_decode(json_encode($data), true);
        }

        return new WsMessage(
            cmd: (int) $packet['cmd'],
            scmd: (int) $packet['scmd'],
            data: is_array($data) ? $data : [],
            fd: $fd,
            account: $account,
        );
    }

    public function encode(int $cmd, int $scmd, array $data): string
    {
        $packet = \App\Game\Core\Packet::packFormat('OK', 0, $data);
        return \App\Game\Core\Packet::packEncode($packet, $cmd, $scmd);
    }
}