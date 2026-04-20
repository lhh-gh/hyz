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
use Swoole\Http\Response;
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
        $token = $request->get['token'] ?? $request->cookie['USER_INFO'] ?? null;
        if (! is_string($token) || $token === '') {
            $server->disconnect($request->fd);
            return;
        }

        $decoded = json_decode($token, true);
        $account = (string) ($decoded['account'] ?? '');
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

    public function onMessage($server,  $frame): void
    {
        try {
            $account = $this->connectionManager->getAccountByFd($frame->fd);
            if ($account === null) {
                throw new BusinessException('连接未登录', 4401);
            }

            $message = $this->codec->decode($frame->data, $frame->fd, $account);
            $this->router->dispatch($server, $message);
        } catch (\Throwable $throwable) {
            $this->exceptionResponder->respond($server, $frame->fd, $throwable);
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $this->connectionManager->unbindByFd($fd);
    }
}