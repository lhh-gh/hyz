<?php

declare(strict_types=1);

namespace App\Infrastructure\Support;

use App\Exception\BusinessException;
use Hyperf\Redis\Redis;

final class DistributedLocker
{
    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    public function lock(string $key, int $ttl = 5): bool
    {
        return (bool) $this->redis->set($key, '1', ['NX', 'EX' => $ttl]);
    }

    public function unlock(string $key): void
    {
        $this->redis->del($key);
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function withLock(string $key, callable $callback, int $ttl = 5): mixed
    {
        if (! $this->lock($key, $ttl)) {
            throw new BusinessException('Operation in progress, please retry', 4290);
        }

        try {
            return $callback();
        } finally {
            $this->unlock($key);
        }
    }
}
