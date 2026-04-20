var Req = {
    timer: 0,

    heartBeat: function (client) {
        clearInterval(this.timer);

        this.timer = setInterval(function () {
            if (client.ws && client.ws.readyState === client.ws.OPEN) {
                client.send({ time: Date.now() }, MainCmd.CMD_SYS, SubCmd.HEARTBEAT_REQ);
                return;
            }

            clearInterval(Req.timer);
        }, 30000);
    },

    RoomCreate: function (client) {
        client.send({}, MainCmd.CMD_GAME, SubCmd.SUB_GAME_ROOM_CREATE);
    },

    RoomJoin: function (client, roomId) {
        client.send({ room_id: roomId }, MainCmd.CMD_GAME, SubCmd.SUB_GAME_ROOM_JOIN);
    },

    RoomSnapshot: function (client) {
        client.send({}, MainCmd.CMD_GAME, SubCmd.ROOM_SNAPSHOT_REQ);
    },

    GameStart: function (client, roomId) {
        client.send({ room_id: roomId || "" }, MainCmd.CMD_GAME, SubCmd.SUB_GAME_START_REQ);
    },

    GameCall: function (client, action) {
        client.send({ action: action, type: action }, MainCmd.CMD_GAME, SubCmd.SUB_GAME_CALL_REQ);
    },

    PlayGame: function (client, cards) {
        client.send({ action: "play", cards: cards, card: cards }, MainCmd.CMD_GAME, SubCmd.SUB_GAME_OUT_CARD_REQ);
    },

    PassGame: function (client) {
        client.send({ action: "pass", cards: [], status: 0 }, MainCmd.CMD_GAME, SubCmd.SUB_GAME_OUT_CARD_REQ);
    },

    ChatMsg: function (client, content) {
        client.send({ content: content }, MainCmd.CMD_GAME, SubCmd.CHAT_MSG_REQ);
    }
};
