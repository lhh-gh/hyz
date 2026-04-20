var MainCmd = {
    CMD_SYS: 1,
    CMD_GAME: 2
};

var SubCmd = {
    LOGIN_SUCCESS_RESP: 1001,
    LOGIN_FAIL_RESP: 1002,
    HEARTBEAT_REQ: 1011,
    HEARTBEAT_RESP: 1012,
    SYSTEM_ERROR_RESP: 1099,
    SUB_GAME_ROOM_CREATE: 2001,
    CREATE_ROOM_SUCC_RESP: 2002,
    CREATE_ROOM_FAIL_RESP: 2003,
    SUB_GAME_ROOM_JOIN: 2011,
    ENTER_ROOM_SUCC_RESP: 2012,
    ENTER_ROOM_FAIL_RESP: 2013,
    ROOM_SNAPSHOT_REQ: 2014,
    ROOM_SNAPSHOT_RESP: 2015,
    SUB_GAME_START_REQ: 2021,
    SUB_GAME_START_RESP: 2022,
    SUB_GAME_CALL_REQ: 2031,
    SUB_GAME_CALL_RESP: 2032,
    SUB_GAME_CALL_TIPS_RESP: 2033,
    SUB_GAME_CATCH_BASECARD_RESP: 2034,
    SUB_GAME_OUT_CARD_REQ: 2041,
    SUB_GAME_OUT_CARD_RESP: 2042,
    SUB_GAME_SETTLEMENT_RESP: 2043,
    CHAT_MSG_REQ: 2051,
    CHAT_MSG_RESP: 2052
};

var Route = {
    1: {
        1002: "loginFail",
        1012: "heartBeat",
        1099: "systemError",
        2002: "createRoomSuccess",
        2003: "createRoomFail",
        2012: "enterRoomSuccess",
        2013: "enterRoomFail"
    },
    2: {
        2015: "roomSnapshot",
        2022: "gameStart",
        2032: "gameCall",
        2033: "gameCallTips",
        2034: "gameCatchBaseCard",
        2042: "gameOutCard",
        2043: "gameSettlement",
        2052: "chatMsg"
    }
};

var CardType = {
    HEITAO: 0,
    HONGTAO: 1,
    MEIHUA: 2,
    FANGKUAI: 3
};

var CardVal = {
    CARD_SAN: "3",
    CARD_SI: "4",
    CARD_WU: "5",
    CARD_LIU: "6",
    CARD_QI: "7",
    CARD_BA: "8",
    CARD_JIU: "9",
    CARD_SHI: "10",
    CARD_J: "J",
    CARD_Q: "Q",
    CARD_K: "K",
    CARD_A: "A",
    CARD_ER: "2",
    CARD_XIAOWANG: "w",
    CARD_DAWANG: "W"
};
