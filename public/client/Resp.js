var Resp = {
    state: {
        account: "",
        roomId: "",
        currentChairId: null,
        myChairId: null,
        landlord: "",
        myCards: [],
        players: [],
        lastPlayedCards: []
    },

    initPage: function (account) {
        this.state.account = account || "";
    },

    heartBeat: function () {},

    loginFail: function (data, envelope) {
        this.showTips("登录失败: " + ((envelope && envelope.message) || "unknown"));
        document.location.href = "/login";
    },

    systemError: function (data, envelope) {
        this.showTips("系统错误: " + ((envelope && envelope.message) || "unknown"));
    },

    createRoomSuccess: function (data) {
        this.state.roomId = data.room_id || this.state.roomId;
        this.setText("roomIdText", this.state.roomId || "-");
        this.setValue("roomIdInput", this.state.roomId || "");
        this.showTips("创建房间成功: " + this.state.roomId);
    },

    createRoomFail: function (data, envelope) {
        this.showTips("创建房间失败: " + ((envelope && envelope.message) || "unknown"));
    },

    enterRoomSuccess: function (data) {
        this.state.roomId = data.room_id || this.state.roomId;
        this.state.players = data.players || [];
        this.captureMyChairId();
        this.renderRoom();
        this.showTips("进入房间成功: " + this.state.roomId);
    },

    enterRoomFail: function (data, envelope) {
        this.showTips("进入房间失败: " + ((envelope && envelope.message) || "unknown"));
    },

    roomSnapshot: function (data) {
        this.applyRoomState(data);
        this.showTips("房间快照已刷新");
    },

    gameStart: function (data) {
        this.state.roomId = data.room_id || this.state.roomId;
        this.state.currentChairId = data.current_chair_id || this.state.currentChairId;
        this.state.myCards = data.my_cards || [];
        this.state.players = data.players || this.state.players;
        this.captureMyChairId();
        this.renderRoom();
        this.showTips("游戏开始");
    },

    gameCall: function (data) {
        this.state.currentChairId = data.current_chair_id || this.state.currentChairId;
        this.state.landlord = data.landlord || this.state.landlord;
        this.renderStatus();
        this.showTips((data.account || "unknown") + " 操作叫地主: " + data.action);
    },

    gameCallTips: function (data) {
        this.showTips("叫地主提示: " + JSON.stringify(data));
    },

    gameCatchBaseCard: function (data) {
        this.state.landlord = data.landlord || this.state.landlord;
        this.state.currentChairId = data.current_chair_id || this.state.currentChairId;
        this.state.myCards = data.my_cards || this.state.myCards;
        this.renderRoom();
        this.showTips("地主确认: " + (data.landlord || "-"));
    },

    gameOutCard: function (data) {
        this.state.currentChairId = data.current_chair_id || this.state.currentChairId;
        this.state.lastPlayedCards = data.last_played_cards || [];

        if (data.account === this.state.account) {
            this.state.myCards = this.removeCards(this.state.myCards, data.cards || []);
        }

        this.renderRoom();
        this.showTips((data.account || "unknown") + " " + (data.action === "pass" ? "过牌" : "出牌") + ": " + JSON.stringify(data.cards || []));
    },

    gameSettlement: function (data) {
        var lines = [];
        var result = data.result || [];

        for (var i = 0; i < result.length; i++) {
            var item = result[i];
            lines.push(item.account + " score=" + item.score + (item.win ? " win" : ""));
        }

        this.setText("settlementText", lines.join(" | ") || "-");
        this.showTips("本局结束, 胜者: " + (data.winner || "-"));
    },

    chatMsg: function (data) {
        this.showTips((data.account || "unknown") + ": " + (data.content || ""));
    },

    applyRoomState: function (data) {
        this.state.roomId = data.room_id || this.state.roomId;
        this.state.currentChairId = data.current_chair_id || this.state.currentChairId;
        this.state.landlord = data.landlord || this.state.landlord;
        this.state.lastPlayedCards = data.last_played_cards || [];
        this.state.myCards = data.my_cards || [];
        this.state.players = data.players || [];
        this.captureMyChairId();
        this.renderRoom();
    },

    captureMyChairId: function () {
        this.state.myChairId = null;

        for (var i = 0; i < this.state.players.length; i++) {
            if (this.state.players[i].account === this.state.account) {
                this.state.myChairId = this.state.players[i].chair_id;
                return;
            }
        }
    },

    renderRoom: function () {
        this.setText("roomIdText", this.state.roomId || "-");
        this.setText("currentChairText", this.state.currentChairId || "-");
        this.setText("landlordText", this.state.landlord || "-");
        this.setText("myChairText", this.state.myChairId || "-");
        this.setText("lastCardsText", JSON.stringify(this.state.lastPlayedCards || []));
        this.renderPlayers();
        this.renderMyCards();
        this.renderStatus();
    },

    renderPlayers: function () {
        var html = "";

        for (var i = 0; i < this.state.players.length; i++) {
            var item = this.state.players[i];
            var desc = [];

            if (item.card_count !== undefined) {
                desc.push("剩余牌数: " + item.card_count);
            }

            if (item.is_landlord) {
                desc.push("地主");
            }

            if (item.online !== undefined) {
                desc.push(item.online ? "在线" : "离线");
            }

            html += '<div class="player-item"><strong>' + item.account + '</strong><span>座位 ' + item.chair_id + '</span><span>' + desc.join(" | ") + "</span></div>";
        }

        document.getElementById("playerList").innerHTML = html || '<div class="empty">暂无玩家</div>';
    },

    renderMyCards: function () {
        var html = "";

        for (var i = 0; i < this.state.myCards.length; i++) {
            var value = this.state.myCards[i];
            html += '<label class="card-item"><input type="checkbox" name="handcard" value="' + value + '"><span>' + this.getCard(value) + "</span></label>";
        }

        document.getElementById("myCards").innerHTML = html || '<div class="empty">暂无手牌</div>';
    },

    renderStatus: function () {
        var myTurn = this.state.myChairId !== null && this.state.currentChairId === this.state.myChairId;

        document.getElementById("call").disabled = !myTurn;
        document.getElementById("nocall").disabled = !myTurn;
        document.getElementById("play").disabled = !myTurn;
        document.getElementById("pass").disabled = !myTurn;
    },

    selectedCards: function () {
        var nodes = document.getElementsByName("handcard");
        var cards = [];

        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].checked) {
                cards.push(parseInt(nodes[i].value, 10));
            }
        }

        return cards;
    },

    removeCards: function (source, used) {
        var result = source.slice();

        for (var i = 0; i < used.length; i++) {
            var index = result.indexOf(used[i]);
            if (index > -1) {
                result.splice(index, 1);
            }
        }

        return result;
    },

    getCard: function (cardVal) {
        if (cardVal === 78) {
            return "joker_" + CardVal.CARD_XIAOWANG;
        }

        if (cardVal === 79) {
            return "JOKER_" + CardVal.CARD_DAWANG;
        }

        var card = "";
        var color = parseInt(cardVal / 16, 10);

        if (color === CardType.HEITAO) {
            card += "♠";
        } else if (color === CardType.HONGTAO) {
            card += "♥";
        } else if (color === CardType.MEIHUA) {
            card += "♣";
        } else if (color === CardType.FANGKUAI) {
            card += "♦";
        }

        var value = parseInt(cardVal % 16, 10);
        var map = {
            1: CardVal.CARD_SAN,
            2: CardVal.CARD_SI,
            3: CardVal.CARD_WU,
            4: CardVal.CARD_LIU,
            5: CardVal.CARD_QI,
            6: CardVal.CARD_BA,
            7: CardVal.CARD_JIU,
            8: CardVal.CARD_SHI,
            9: CardVal.CARD_J,
            10: CardVal.CARD_Q,
            11: CardVal.CARD_K,
            12: CardVal.CARD_A,
            13: CardVal.CARD_ER
        };

        return card + "_" + (map[value] || cardVal);
    },

    showTips: function (tips) {
        var el = document.getElementById("msgText");
        var line = "[" + new Date().toLocaleTimeString() + "] " + tips;
        el.value = el.value ? el.value + "\n" + line : line;
        el.scrollTop = el.scrollHeight;
    },

    setText: function (id, text) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = text;
        }
    },

    setValue: function (id, value) {
        var el = document.getElementById(id);
        if (el) {
            el.value = value;
        }
    }
};
