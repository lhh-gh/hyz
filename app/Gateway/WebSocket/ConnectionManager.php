<?php

declare(strict_types=1);

namespace App\Gateway\WebSocket;

use App\Infrastructure\Persistence\Redis\RedisKey;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;

final class ConnectionManager
{
    public function __construct(
        private readonly Redis $redis,
        private readonly ConfigInterface $config,
    ) {
    }

    public function bind(int $fd, string $account): void
    {
        $ttl = (int) $this->config->get('game.connection.ttl', 86400);
        $this->redis->setex(RedisKey::connectionByFd($fd), $ttl, $account);
        $this->redis->setex(RedisKey::connectionByAccount($account), $ttl, (string) $fd);
    }

    public function getAccountByFd(int $fd): ?string
    {
        $value = $this->redis->get(RedisKey::connectionByFd($fd));

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function getFdByAccount(string $account): ?int
    {
        $value = $this->redis->get(RedisKey::connectionByAccount($account));

        return is_string($value) && $value !== '' ? (int) $value : null;
    }

    public function isOnline(string $account): bool
    {
        return $this->getFdByAccount($account) !== null;
    }

    public function unbindByFd(int $fd): void
    {
        $account = $this->getAccountByFd($fd);
        $this->redis->del(RedisKey::connectionByFd($fd));

        if ($account !== null) {
            $this->redis->del(RedisKey::connectionByAccount($account));
        }
    }
}
