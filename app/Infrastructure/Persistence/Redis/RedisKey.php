<?php


declare(strict_types=1);

namespace App\Infrastructure\Persistence\Redis;

/**
 *
 *  redis 数据模型重设计
 * 统一 Key 命名
 *
 */
final class RedisKey
{
    public static function connectionByFd(int $fd): string
    {
        return "ddz:connection:fd:{$fd}";
    }

    public static function connectionByAccount(string $account): string
    {
        return "ddz:connection:account:{$account}";
    }

    public static function room(string $roomId): string
    {
        return "ddz:room:{$roomId}";
    }

    public static function roomPlayerIndex(string $account): string
    {
        return "ddz:player:room:{$account}";
    }

    public static function lockRoom(string $roomId): string
    {
        return "ddz:lock:room:{$roomId}";
    }

    public static function lockAccount(string $account): string
    {
        return "ddz:lock:account:{$account}";
    }
}