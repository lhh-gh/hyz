<?php

declare(strict_types=1);

use App\Application\Game\Handler\CallLandlordHandler;
use App\Application\Game\Handler\ChatMessageHandler;
use App\Application\Game\Handler\CreateRoomHandler;
use App\Application\Game\Handler\HeartbeatHandler;
use App\Application\Game\Handler\JoinRoomHandler;
use App\Application\Game\Handler\PlayCardHandler;
use App\Application\Game\Handler\RoomSnapshotHandler;
use App\Application\Game\Handler\StartGameHandler;
use App\Constants\MainCmd;
use App\Constants\SubCmd;

return [
    MainCmd::CMD_SYS => [
        SubCmd::HEARTBEAT_REQ => HeartbeatHandler::class,
    ],
    MainCmd::CMD_GAME => [
        SubCmd::SUB_GAME_ROOM_CREATE => CreateRoomHandler::class,
        SubCmd::SUB_GAME_ROOM_JOIN => JoinRoomHandler::class,
        SubCmd::ROOM_SNAPSHOT_REQ => RoomSnapshotHandler::class,
        SubCmd::SUB_GAME_START_REQ => StartGameHandler::class,
        SubCmd::SUB_GAME_CALL_REQ => CallLandlordHandler::class,
        SubCmd::SUB_GAME_OUT_CARD_REQ => PlayCardHandler::class,
        SubCmd::CHAT_MSG_REQ => ChatMessageHandler::class,
    ],
];
