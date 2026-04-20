<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit;

use App\Gateway\WebSocket\ConnectionManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use PHPUnit\Framework\TestCase;

final class ConnectionManagerTest extends TestCase
{
    public function testBindUsesConfiguredTtl(): void
    {
        $redis = $this->createMock(Redis::class);
        $config = $this->createMock(ConfigInterface::class);

        $config->expects($this->once())
            ->method('get')
            ->with('game.connection.ttl', 86400)
            ->willReturn(120);

        $redis->expects($this->exactly(2))
            ->method('setex')
            ->willReturnCallback(static function (string $key, int $ttl, string $value): bool {
                return in_array($key, ['ddz:connection:fd:7', 'ddz:connection:account:alice'], true)
                    && $ttl === 120
                    && in_array($value, ['alice', '7'], true);
            });

        $manager = new ConnectionManager($redis, $config);
        $manager->bind(7, 'alice');
    }
}
