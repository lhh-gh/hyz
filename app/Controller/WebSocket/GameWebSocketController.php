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

        $query = is_array($request->get ?? null) ? $request->get : [];
        $cookies = is_array($request->cookie ?? null) ? $request->cookie : [];
        $token = $query['token'] ?? $cookies['USER_INFO'] ?? null;

        if (! is_string($token) || $token === '') {
            error_log(sprintf(
                '[ws:onOpen] missing token fd=%d query=%s cookies=%s',
                $request->fd,
                json_encode($query, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
                json_encode(array_keys($cookies), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]',
            ));
            $server->disconnect($request->fd);

            return;
        }

        $decoded = json_decode(urldecode($token), true);
        $account = is_array($decoded) ? (string) ($decoded['account'] ?? '') : '';
        if ($account === '') {
            error_log(sprintf(
                '[ws:onOpen] invalid token fd=%d token=%s decoded=%s',
                $request->fd,
                $token,
                json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null',
            ));
            $server->disconnect($request->fd);

            return;
        }

        $oldFd = $this->connectionManager->getFdByAccount($account);
        if ($oldFd !== null && $oldFd !== $request->fd && $server->isEstablished($oldFd)) {
            $server->disconnect($oldFd);
        }

        $this->connectionManager->bind($request->fd, $account);
        error_log(sprintf('[ws:onOpen] bind success fd=%d account=%s', $request->fd, $account));
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
            error_log(sprintf(
                '[ws:onMessage] exception fd=%d class=%s message=%s trace=%s',
                $frame instanceof Frame ? $frame->fd : 0,
                get_class($throwable),
                $throwable->getMessage(),
                $throwable->getTraceAsString(),
            ));
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
