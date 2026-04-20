var Init = {
    ws: null,
    url: "",
    reconnectTimer: 0,
    reconnectTimes: 20,
    debug: true,

    webSock: function (url) {
        var client = this;
        this.url = url;
        this.ws = new WebSocket(url);
        this.ws.binaryType = "arraybuffer";

        this.ws.onopen = function () {
            clearInterval(client.reconnectTimer);
            client.reconnectTimes = 20;
            Req.heartBeat(client);
            Resp.showTips("WebSocket 已连接");
        };

        this.ws.onmessage = function (evt) {
            if (!evt.data) {
                return;
            }

            var totalData = new DataView(evt.data);
            var offset = 0;

            while (offset < totalData.byteLength) {
                var len = totalData.getUint32(offset);
                var packetData = evt.data.slice(offset, offset + len + 4);
                client.recvCmd(Packet.msgunpack(packetData));
                offset += len + 4;
            }
        };

        this.ws.onclose = function () {
            Resp.showTips("WebSocket 已断开");
            clearInterval(Req.timer);
        };

        this.ws.onerror = function (evt) {
            client.log("WebSocket error: " + evt.type);
        };

        return this;
    },

    recvCmd: function (packet) {
        var routeGroup = Route[packet.cmd] || {};
        var func = routeGroup[packet.scmd];
        var envelope = packet.data || {};
        var data = envelope && typeof envelope === "object" && envelope.data !== undefined ? envelope.data : envelope;

        if (!func || typeof Resp[func] !== "function") {
            Resp.showTips("未处理消息 cmd=" + packet.cmd + " scmd=" + packet.scmd);
            return;
        }

        Resp[func](data || {}, envelope || {}, packet);
    },

    send: function (data, cmd, scmd) {
        if (!this.ws || this.ws.readyState !== this.ws.OPEN) {
            Resp.showTips("WebSocket 未连接");
            return;
        }

        this.ws.send(Packet.msgpack(data, cmd, scmd));
    },

    log: function (msg) {
        if (this.debug) {
            console.log(msg);
        }
    }
};
