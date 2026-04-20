<?php

declare(strict_types=1);

namespace App\Application\Gateway;

use App\DTO\WsMessage;
use App\Exception\UnsupportedCommandException;
use Psr\Container\ContainerInterface;
use Swoole\WebSocket\Server;
use Hyperf\Contract\ConfigInterface;
final class WsRouter
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
    ) {
    }

    public function dispatch(Server $server, WsMessage $message): void
    {
        $routes = $this->config->get('ws-routes', []);
        $handlerClass = $routes[$message->cmd][$message->scmd] ?? null;

        if (! is_string($handlerClass) || ! $this->container->has($handlerClass)) {
            throw new UnsupportedCommandException($message->cmd, $message->scmd);
        }

        /** @var WsHandlerInterface $handler */
        $handler = $this->container->get($handlerClass);
        $handler->handle($server, $message);
    }
}