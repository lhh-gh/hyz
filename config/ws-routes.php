<?php
declare(strict_types=1);

use App\Application\Game\Handler\CallLandlordHandler;
use App\Application\Game\Handler\CreateRoomHandler;
use App\Application\Game\Handler\JoinRoomHandler;
use App\Application\Game\Handler\PlayCardHandler;
use App\Constants\MainCmd;
use App\Constants\SubCmd;

return [
    MainCmd::CMD_GAME => [
        SubCmd::SUB_GAME_ROOM_CREATE => CreateRoomHandler::class,
        SubCmd::SUB_GAME_ROOM_JOIN => JoinRoomHandler::class,
        SubCmd::SUB_GAME_CALL_REQ => CallLandlordHandler::class,
        SubCmd::SUB_GAME_OUT_CARD_REQ => PlayCardHandler::class,
    ],
];