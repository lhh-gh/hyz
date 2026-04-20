<?php


declare(strict_types=1);

namespace App\Exception;

final class InvalidPacketException extends BusinessException
{
    public function __construct(string $message = '非法协议包')
    {
        parent::__construct($message, 4001);
    }
}