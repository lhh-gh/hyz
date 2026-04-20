<?php
declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Gateway\WsHandlerInterface;
use App\Application\Game\CallLandlordAppService;
use App\DTO\WsMessage;
use Swoole\WebSocket\Server;

final class CallLandlordHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly CallLandlordAppService $service,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $this->service->execute(
            $server,
            (string) $message->account,
            (int) ($message->data['type'] ?? 0),
        );
    }
}