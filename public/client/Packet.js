var Packet = {
    msgunpack: function (buff) {
        var body = "";
        var len = new DataView(buff, 0, 4).getUint32(0);
        var bodyData = new DataView(buff, 4, len);
        var cmd = bodyData.getUint8(0);
        var scmd = bodyData.getUint8(1);

        for (var i = 2; i < bodyData.byteLength; i++) {
            body += String.fromCharCode(bodyData.getUint8(i));
        }

        body = msgpack.unpack(body);
        body.cmd = cmd;
        body.scmd = scmd;
        body.len = len + 4;

        return body;
    },

    msgpack: function (data, cmd, scmd) {
        var dataBuff = msgpack.pack(data);
        var body = String.fromCharCode.apply(null, new Uint8Array(dataBuff));
        var len = body.length + 6;
        var buf = new ArrayBuffer(len);
        var view = new DataView(buf, 0, len);

        view.setUint32(0, body.length + 2);
        view.setUint8(4, cmd);
        view.setUint8(5, scmd);

        for (var i = 0; i < body.length; i++) {
            view.setInt8(i + 6, body.charCodeAt(i));
        }

        return buf;
    }
};
