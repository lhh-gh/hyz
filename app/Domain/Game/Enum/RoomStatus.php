<?php

declare(strict_types=1);

namespace App\Domain\Game\Enum;

enum RoomStatus: string
{
    case Waiting = 'waiting';
    case Ready = 'ready';
    case Calling = 'calling';
    case Playing = 'playing';
    case Finished = 'finished';
}