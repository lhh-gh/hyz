<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Game\PlayCardAppService;
use App\Application\Gateway\WsHandlerInterface;
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
        $action = $message->data['action'] ?? null;
        $cards = $message->data['cards'] ?? $message->data['card'] ?? [];

        if ($action === null) {
            $legacyStatus = (int) ($message->data['status'] ?? 1);
            $action = $legacyStatus === 0 ? 'pass' : 'play';
        }

        $this->service->execute(
            server: $server,
            account: (string) $message->account,
            cards: is_array($cards) ? $cards : [],
            pass: $action === 'pass',
        );
    }
}
