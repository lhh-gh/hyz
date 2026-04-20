<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页</title>
    <style>
        body {
            margin: 0;
            font-family: "Microsoft YaHei", sans-serif;
            background: #f3f6fb;
            color: #1f2937;
        }

        .wrap {
            max-width: 880px;
            margin: 48px auto;
            padding: 0 20px;
        }

        .hero {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 30px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }

        .meta {
            margin: 8px 0;
            color: #475467;
            font-size: 14px;
        }

        .panel {
            margin-top: 24px;
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 30px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }

        code {
            padding: 2px 6px;
            border-radius: 6px;
            background: #eef2ff;
            color: #3730a3;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1>斗地主联调首页</h1>
        <div class="meta">当前账号：<strong>{{ $account ?? '' }}</strong></div>
        <div class="meta">当前 Host：<strong>{{ $host ?? '' }}</strong></div>
    </div>

    <div class="panel">
        <p>HTTP 登录页已经可用，下一步请继续联调 WebSocket 主流程。</p>
        <p>建议优先验证：<code>创建房间 -> 加入房间 -> 自动开局 -> 叫地主 -> 出牌 -> 结算</code></p>
    </div>
</div>
</body>
</html>
