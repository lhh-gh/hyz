<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Gateway\WsHandlerInterface;
use App\Application\Game\PlayCardAppService;
use App\DTO\WsMessage;
use Swoole\WebSocket\Server;

final class PlayCardHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly PlayCardAppService $service,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $this->service->execute(
            server: $server,
            account: (string) $message->account,
            cards: $message->data['card'] ?? [],
            pass: ((int) ($message->data['status'] ?? 1)) === 0,
        );
    }
}