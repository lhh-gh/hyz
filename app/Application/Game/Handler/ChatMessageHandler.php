<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Game\ChatMessageService;
use App\Application\Gateway\WsHandlerInterface;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class ChatMessageHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly ChatMessageService $service,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $payload = $this->service->execute(
            (string) $message->account,
            (string) ($message->data['content'] ?? ''),
        );

        $fds = $payload['fds'];
        unset($payload['fds']);

        $this->pusher->pushToMany(
            $server,
            $fds,
            MainCmd::CMD_GAME,
            SubCmd::CHAT_MSG_RESP,
            [
                'status' => 'success',
                'code' => 0,
                'message' => 'chat sent',
                'data' => $payload,
            ],
        );
    }
}
