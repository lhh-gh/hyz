<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>斗地主联调页</title>
    <style>
        :root {
            --bg: #eef2f7;
            --panel: #ffffff;
            --line: #d9e2ec;
            --text: #102a43;
            --muted: #486581;
            --primary: #0f766e;
            --primary-hover: #115e59;
            --warn: #b91c1c;
        }

        body {
            margin: 0;
            font-family: "Microsoft YaHei", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 24%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 20%),
                var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1200px;
            margin: 32px auto 48px;
            padding: 0 20px;
        }

        .hero {
            background: linear-gradient(135deg, #0f172a, #164e63);
            color: #f8fafc;
            border-radius: 24px;
            padding: 28px 32px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 32px;
        }

        .meta {
            margin: 8px 0;
            color: rgba(248, 250, 252, 0.85);
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }

        .panel {
            margin-top: 24px;
            background: var(--panel);
            border-radius: 20px;
            padding: 24px 26px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }

        .panel h2 {
            margin: 0 0 16px;
            font-size: 20px;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .row-1 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--muted);
        }

        input[type="text"],
        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            background: #fff;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }

        button {
            border: 0;
            border-radius: 12px;
            padding: 10px 16px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background: var(--primary-hover);
        }

        button.warn {
            background: var(--warn);
        }

        button:disabled {
            background: #9aa5b1;
            cursor: not-allowed;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            background: #f8fbff;
        }

        .stat small {
            display: block;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .card-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 8px 10px;
            background: #fff;
        }

        .player-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .player-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            background: #fbfdff;
        }

        .empty {
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .grid,
            .row,
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1>斗地主 Hyperf 3.0 联调页</h1>
        <div class="meta">当前账号: <strong id="accountText">{{ $account ?? '' }}</strong></div>
        <div class="meta">当前 Host: <strong>{{ $host ?? '' }}</strong></div>
        <div class="meta">建议流程: 创建房间 -> 其他账号加入 -> 自动发牌 -> 叫地主 -> 出牌 -> 结算</div>
    </div>

    <div class="grid">
        <div>
            <div class="panel">
                <h2>连接与操作</h2>
                <div class="row-1">
                    <div>
                        <label for="wsUrl">WebSocket 地址</label>
                        <input id="wsUrl" type="text" value="">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="roomIdInput">房间号</label>
                        <input id="roomIdInput" type="text" placeholder="创建后自动回填, 或手动输入加入">
                    </div>
                    <div>
                        <label for="chatInput">聊天内容</label>
                        <input id="chatInput" type="text" placeholder="输入聊天消息">
                    </div>
                </div>

                <div class="actions">
                    <button id="connectBtn" type="button">连接 WS</button>
                    <button id="createRoomBtn" type="button">创建房间</button>
                    <button id="joinRoomBtn" type="button">加入房间</button>
                    <button id="snapshotBtn" type="button">房间快照</button>
                    <button id="startBtn" type="button">开始游戏</button>
                    <button id="chatBtn" type="button">发送聊天</button>
                </div>

                <div class="actions">
                    <button id="call" type="button" disabled>叫地主</button>
                    <button id="nocall" type="button" disabled>不叫</button>
                    <button id="play" type="button" disabled>出牌</button>
                    <button id="pass" type="button" class="warn" disabled>过牌</button>
                </div>
            </div>

            <div class="panel">
                <h2>我的手牌</h2>
                <div id="myCards" class="card-list">
                    <div class="empty">连接并开局后显示手牌</div>
                </div>
            </div>

            <div class="panel">
                <h2>日志</h2>
                <textarea id="msgText" readonly></textarea>
            </div>
        </div>

        <div>
            <div class="panel">
                <h2>房间状态</h2>
                <div class="stats">
                    <div class="stat"><small>房间号</small><div id="roomIdText">-</div></div>
                    <div class="stat"><small>我的座位</small><div id="myChairText">-</div></div>
                    <div class="stat"><small>当前操作座位</small><div id="currentChairText">-</div></div>
                    <div class="stat"><small>地主账号</small><div id="landlordText">-</div></div>
                    <div class="stat"><small>最近出牌</small><div id="lastCardsText">-</div></div>
                    <div class="stat"><small>结算</small><div id="settlementText">-</div></div>
                </div>
            </div>

            <div class="panel">
                <h2>玩家列表</h2>
                <div id="playerList" class="player-list">
                    <div class="empty">暂无玩家</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.DDZ_PAGE = {
        account: @json($account ?? ''),
        host: @json($host ?? ''),
    };
</script>
<script src="/client/msgpack.js"></script>
<script src="/client/Const.js"></script>
<script src="/client/Packet.js"></script>
<script src="/client/Req.js"></script>
<script src="/client/Resp.js"></script>
<script src="/client/Init.js"></script>
<script>
    (function () {
        var account = window.DDZ_PAGE.account || "";
        var host = window.DDZ_PAGE.host || "127.0.0.1:9501";
        var wsHost = host.indexOf(":") > -1 ? host.replace(/:\d+$/, ":9502") : host + ":9502";
        var token = encodeURIComponent(JSON.stringify({ account: account }));
        var defaultWsUrl = "ws://" + wsHost + "/?token=" + token;

        document.getElementById("wsUrl").value = defaultWsUrl;
        Resp.initPage(account);

        document.getElementById("connectBtn").addEventListener("click", function () {
            Init.webSock(document.getElementById("wsUrl").value.trim());
        });

        document.getElementById("createRoomBtn").addEventListener("click", function () {
            Req.RoomCreate(Init);
        });

        document.getElementById("joinRoomBtn").addEventListener("click", function () {
            Req.RoomJoin(Init, document.getElementById("roomIdInput").value.trim());
        });

        document.getElementById("snapshotBtn").addEventListener("click", function () {
            Req.RoomSnapshot(Init);
        });

        document.getElementById("startBtn").addEventListener("click", function () {
            Req.GameStart(Init, document.getElementById("roomIdInput").value.trim());
        });

        document.getElementById("chatBtn").addEventListener("click", function () {
            Req.ChatMsg(Init, document.getElementById("chatInput").value.trim());
        });

        document.getElementById("call").addEventListener("click", function () {
            Req.GameCall(Init, 1);
        });

        document.getElementById("nocall").addEventListener("click", function () {
            Req.GameCall(Init, 0);
        });

        document.getElementById("play").addEventListener("click", function () {
            Req.PlayGame(Init, Resp.selectedCards());
        });

        document.getElementById("pass").addEventListener("click", function () {
            Req.PassGame(Init);
        });
    })();
</script>
</body>
</html>
