<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <style>
        body {
            margin: 0;
            font-family: "Microsoft YaHei", sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #d7e1ec);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 360px;
            background: #fff;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 18px 40px rgba(28, 43, 63, 0.12);
        }

        h1 {
            margin: 0 0 20px;
            font-size: 24px;
            color: #1f2d3d;
        }

        p {
            margin: 0 0 16px;
            color: #5b6878;
            font-size: 14px;
        }

        .error {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff1f1;
            color: #b42318;
            font-size: 14px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #344054;
            font-size: 14px;
        }

        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
        }

        input[type="text"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        button {
            width: 100%;
            margin-top: 18px;
            border: 0;
            border-radius: 10px;
            padding: 12px 16px;
            background: #2563eb;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>斗地主登录</h1>
    <p>请输入一个账号用于本地联调。</p>

    @if (!empty($tips ?? ''))
        <div class="error">{{ $tips }}</div>
    @endif

    <form method="post" action="/login">
        <input type="hidden" name="action" value="login">

        <label for="account">账号</label>
        <input id="account" type="text" name="account" placeholder="例如 alice" autocomplete="off">

        <button type="submit">进入系统</button>
    </form>
</div>
</body>
</html>
