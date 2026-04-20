<?php

declare(strict_types=1);

namespace App\Controller\WebSocket;

use App\Application\Gateway\WsRouter;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\PacketCodec;
use App\Support\WsExceptionResponder;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

final class GameWebSocketController implements OnOpenInterface, OnMessageInterface, OnCloseInterface
{
    public function __construct(
        private readonly PacketCodec $codec,
        private readonly WsRouter $router,
        private readonly ConnectionManager $connectionManager,
        private readonly WsExceptionResponder $exceptionResponder,
    ) {
    }

    public function onOpen($server, $request): void
    {
        if (! $server instanceof Server || ! $request instanceof Request) {
            return;
        }

        $token = $request->get['token'] ?? $request->cookie['USER_INFO'] ?? null;
        if (! is_string($token) || $token === '') {
            $server->disconnect($request->fd);

            return;
        }

        $decoded = json_decode($token, true);
        $account = is_array($decoded) ? (string) ($decoded['account'] ?? '') : '';
        if ($account === '') {
            $server->disconnect($request->fd);

            return;
        }

        $oldFd = $this->connectionManager->getFdByAccount($account);
        if ($oldFd !== null && $oldFd !== $request->fd && $server->isEstablished($oldFd)) {
            $server->disconnect($oldFd);
        }

        $this->connectionManager->bind($request->fd, $account);
    }

    public function onMessage($server, $frame): void
    {
        try {
            if (! $server instanceof Server || ! $frame instanceof Frame) {
                throw new BusinessException('Invalid websocket context', 5001);
            }

            $account = $this->connectionManager->getAccountByFd($frame->fd);
            if ($account === null) {
                throw new BusinessException('Connection is not authenticated', 4401);
            }

            $message = $this->codec->decode($frame->data, $frame->fd, $account);
            $this->router->dispatch($server, $message);
        } catch (\Throwable $throwable) {
            if ($server instanceof Server && $frame instanceof Frame) {
                $this->exceptionResponder->respond($server, $frame->fd, $throwable);
            }
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $this->connectionManager->unbindByFd($fd);
    }
}
