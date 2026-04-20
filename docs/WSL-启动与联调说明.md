# WSL 启动与联调说明

## 目标

本文档用于说明如何在 `WSL Ubuntu 22.04` 环境下启动当前 `hyperf-skeleton` 项目，并完成最基本的本地联调。

内容包括：

- PHP 8.2 环境准备
- 必要扩展安装
- Redis 启动
- Hyperf 启动
- HTTP / WebSocket 入口验证
- 三客户端联调流程

---

## 1. 安装系统依赖

先安装编译工具和 PHP 8.2 基础包：

```bash
sudo apt update
sudo apt install -y \
  git unzip curl build-essential pkg-config autoconf bison re2c \
  libssl-dev libcurl4-openssl-dev libpcre3-dev zlib1g-dev \
  php8.2 php8.2-cli php8.2-dev php8.2-common php8.2-mbstring php8.2-xml \
  php8.2-curl php8.2-zip php8.2-redis php8.2-mysql \
  php-pear composer
```

---

## 2. 安装 `msgpack` 扩展

当前项目协议编码依赖 `msgpack`，如果没有这个扩展，`Packet.php` 无法正常工作。

执行：

```bash
sudo pecl install msgpack
echo "extension=msgpack.so" | sudo tee /etc/php/8.2/mods-available/msgpack.ini
sudo phpenmod msgpack
```

验证：

```bash
php -r "var_dump(function_exists('msgpack_pack'));"
```

预期输出：

```php
bool(true)
```

---

## 3. 安装 `swoole` 扩展

Hyperf 运行和测试都依赖 `swoole`。

执行：

```bash
sudo pecl install swoole
echo "extension=swoole.so" | sudo tee /etc/php/8.2/mods-available/swoole.ini
sudo phpenmod swoole
```

验证：

```bash
php -m | grep swoole
php --ri swoole
```

---

## 4. 检查 `redis` 扩展

如果你已经安装了 `php8.2-redis`，通常就够了。

验证：

```bash
php -m | grep redis
```

---

## 5. 进入项目目录

```bash
cd /home/phpgo/projects/php-demo/hyperf-skeleton
```

如果你的项目不在这个目录，请替换成实际路径。

---

## 6. 安装 Composer 依赖

```bash
composer install
```

---

## 7. 准备 `.env`

如果 `.env` 不存在，先复制：

```bash
cp .env.example .env
```

然后重点确认以下配置是否正确：

- Redis host
- Redis port
- Redis password
- HTTP / WebSocket 端口

重点参考：

- [config/autoload/redis.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/redis.php)
- [config/autoload/server.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/server.php)

---

## 8. 安装并启动 Redis

如果当前机器还没启动 Redis：

```bash
sudo apt install -y redis-server
sudo service redis-server start
redis-cli ping
```

预期输出：

```text
PONG
```

---

## 9. 启动 Hyperf

前台启动：

```bash
php bin/hyperf.php start
```

后台启动：

```bash
php bin/hyperf.php start -d
```

---

## 10. 验证 PHP 运行环境

启动前后都建议执行一次：

```bash
php -v
php -m | grep -E "swoole|msgpack|redis"
php -r "var_dump(function_exists('msgpack_pack'));"
```

必须保证：

- `swoole` 已启用
- `msgpack` 已启用
- `redis` 已启用

---

## 11. 验证 HTTP 页面入口

默认访问：

```text
http://127.0.0.1:9501/login
```

如果端口不是 `9501`，以：

- [config/autoload/server.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/server.php)

中的实际配置为准。

需要确认：

- `/login` 页面可以打开
- 输入账号后可以跳转到 `/`
- `USER_INFO` cookie 正常写入

相关文件：

- [config/routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/routes.php)
- [app/Controller/IndexController.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Controller/IndexController.php)

---

## 12. 验证 WebSocket 入口

默认 WebSocket 地址一般是：

```text
ws://127.0.0.1:9502/
```

实际端口仍然以：

- [config/autoload/server.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/server.php)

为准。

当前连接认证来源有两个：

- query 参数中的 `token`
- cookie 中的 `USER_INFO`

最简单的联调方式是：

1. 先通过 `/login` 登录
2. 再让页面或测试客户端建立 WebSocket 连接

---

## 13. 推荐联调顺序

建议按下面顺序验证，不要一上来直接测出牌：

1. 心跳
2. 创建房间
3. 第二个玩家加入
4. 第三个玩家加入
5. 自动开局
6. 发牌
7. 叫地主
8. 地主确认与底牌
9. 出牌
10. 过牌
11. 游戏结束
12. 结算广播
13. 房间快照恢复
14. 房间聊天

---

## 14. 推荐三客户端联调

建议准备 3 个不同账号：

- `alice`
- `bob`
- `carol`

联调流程：

1. `alice` 登录并创建房间
2. `bob` 加入房间
3. `carol` 加入房间
4. 验证是否自动开局
5. 三人依次叫地主
6. 验证地主确认广播
7. 多轮出牌与过牌
8. 一方出完牌后验证结算
9. 任一玩家断开后重连并请求房间快照
10. 任一玩家发送聊天并验证只广播给房间成员

---

## 15. 当前协议入口文件

协议路由定义：

- [config/ws-routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/ws-routes.php)

协议常量定义：

- [app/Constants/SubCmd.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Constants/SubCmd.php)

WebSocket 入口：

- [app/Controller/WebSocket/GameWebSocketController.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Controller/WebSocket/GameWebSocketController.php)

协议分发：

- [app/Application/Gateway/WsRouter.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Application/Gateway/WsRouter.php)

编解码：

- [app/Gateway/WebSocket/PacketCodec.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Gateway/WebSocket/PacketCodec.php)

---

## 16. 常见问题

### 16.1 `msgpack_pack()` 未定义

说明：

- `ext-msgpack` 没装或没启用

检查：

```bash
php -r "var_dump(function_exists('msgpack_pack'));"
```

---

### 16.2 `The ext-swoole is required`

说明：

- `ext-swoole` 没装或没启用

检查：

```bash
php -m | grep swoole
```

---

### 16.3 WebSocket 能连上但没有业务响应

优先检查：

- [app/Gateway/WebSocket/PacketCodec.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Gateway/WebSocket/PacketCodec.php)
- [app/Application/Gateway/WsRouter.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Application/Gateway/WsRouter.php)
- [config/ws-routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/ws-routes.php)

---

### 16.4 登录页打不开

优先检查：

- [config/routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/routes.php)
- [app/Controller/IndexController.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Controller/IndexController.php)

---

## 17. 启动成功的最低标准

在你认为“环境已经可用”之前，至少保证以下几点全部成立：

1. `php bin/hyperf.php start` 能成功启动
2. `/login` 页面能打开
3. WebSocket 握手能成功
4. 三人完整对局至少能手工跑通一轮
5. `swoole` 和 `msgpack` 都已启用

---

## 18. 最后建议

如果你当前代码已经完成重构主流程，那么后续最重要的不是继续写功能，而是：

1. 把环境装对
2. 把三客户端联调跑通
3. 把测试真正跑起来

做到这三点，项目才算真正进入“可交付”状态。

