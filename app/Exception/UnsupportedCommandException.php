<?php
declare(strict_types=1);

namespace App\Exception;

final class UnsupportedCommandException extends BusinessException
{
    public function __construct(int $cmd, int $scmd)
    {
        parent::__construct("不支持的协议 cmd={$cmd}, scmd={$scmd}", 4002);
    }
}