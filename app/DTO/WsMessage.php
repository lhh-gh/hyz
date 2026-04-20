<?php
declare(strict_types=1);

namespace App\DTO;
/**
 *
 * WebSocket 消息入口重构
 * DTO：统一消息对象
 */

final readonly class WsMessage
{
    public function __construct(
        public int $cmd,
        public int $scmd,
        public array $data,
        public int $fd,
        public ?string $account,
    ) {
    }
}