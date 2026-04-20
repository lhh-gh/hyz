<?php

declare(strict_types=1);

namespace App\Constants;

final class SubCmd
{
    public const LOGIN_SUCCESS_RESP = 1001;
    public const LOGIN_FAIL_RESP = 1002;
    public const HEARTBEAT_REQ = 1011;
    public const HEARTBEAT_RESP = 1012;
    public const SYSTEM_ERROR_RESP = 1099;

    public const SUB_GAME_ROOM_CREATE = 2001;
    public const CREATE_ROOM_SUCC_RESP = 2002;
    public const CREATE_ROOM_FAIL_RESP = 2003;

    public const SUB_GAME_ROOM_JOIN = 2011;
    public const ENTER_ROOM_SUCC_RESP = 2012;
    public const ENTER_ROOM_FAIL_RESP = 2013;
    public const ROOM_SNAPSHOT_REQ = 2014;
    public const ROOM_SNAPSHOT_RESP = 2015;

    public const SUB_GAME_START_REQ = 2021;
    public const SUB_GAME_START_RESP = 2022;

    public const SUB_GAME_CALL_REQ = 2031;
    public const SUB_GAME_CALL_RESP = 2032;
    public const SUB_GAME_CALL_TIPS_RESP = 2033;
    public const SUB_GAME_CATCH_BASECARD_RESP = 2034;

    public const SUB_GAME_OUT_CARD_REQ = 2041;
    public const SUB_GAME_OUT_CARD_RESP = 2042;
    public const SUB_GAME_SETTLEMENT_RESP = 2043;

    public const CHAT_MSG_REQ = 2051;
    public const CHAT_MSG_RESP = 2052;
}
