<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit;

use App\Application\Game\Handler\HeartbeatHandler;
use App\Application\Game\Handler\ChatMessageHandler;
use App\Application\Game\Handler\RoomSnapshotHandler;
use App\Application\Game\Handler\StartGameHandler;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use PHPUnit\Framework\TestCase;

final class WsRoutesConfigTest extends TestCase
{
    public function testWsRoutesContainHeartbeatAndStartGameMappings(): void
    {
        $routes = require BASE_PATH . '/config/ws-routes.php';

        $this->assertSame(HeartbeatHandler::class, $routes[MainCmd::CMD_SYS][SubCmd::HEARTBEAT_REQ]);
        $this->assertSame(StartGameHandler::class, $routes[MainCmd::CMD_GAME][SubCmd::SUB_GAME_START_REQ]);
        $this->assertSame(RoomSnapshotHandler::class, $routes[MainCmd::CMD_GAME][SubCmd::ROOM_SNAPSHOT_REQ]);
        $this->assertSame(ChatMessageHandler::class, $routes[MainCmd::CMD_GAME][SubCmd::CHAT_MSG_REQ]);
    }
}
