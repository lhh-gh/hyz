# WSL Ubuntu 22.04 Setup Guide

## Goal

This document describes how to prepare a WSL Ubuntu 22.04 environment for running the Hyperf 3 DDZ skeleton.

It covers:

- PHP 8.2 installation
- required extensions
- Redis startup
- Composer install
- Hyperf startup
- basic verification

## 1. Install system packages

```bash
sudo apt update
sudo apt install -y \
  git unzip curl build-essential pkg-config autoconf bison re2c \
  libssl-dev libcurl4-openssl-dev libpcre3-dev zlib1g-dev \
  php8.2 php8.2-cli php8.2-dev php8.2-common php8.2-mbstring php8.2-xml \
  php8.2-curl php8.2-zip php8.2-redis php8.2-mysql \
  php-pear composer
```

## 2. Install `msgpack`

```bash
sudo pecl install msgpack
echo "extension=msgpack.so" | sudo tee /etc/php/8.2/mods-available/msgpack.ini
sudo phpenmod msgpack
```

Verify:

```bash
php -r "var_dump(function_exists('msgpack_pack'));"
```

Expected:

```php
bool(true)
```

## 3. Install `swoole`

```bash
sudo pecl install swoole
echo "extension=swoole.so" | sudo tee /etc/php/8.2/mods-available/swoole.ini
sudo phpenmod swoole
```

Verify:

```bash
php -m | grep swoole
php --ri swoole
```

## 4. Verify `redis` extension

```bash
php -m | grep redis
```

## 5. Enter project directory

```bash
cd /home/phpgo/projects/php-demo/hyperf-skeleton
```

If your project path is different, replace it with your actual path.

## 6. Install Composer dependencies

```bash
composer install
```

## 7. Prepare `.env`

If `.env` does not exist:

```bash
cp .env.example .env
```

Then verify:

- Redis host
- Redis port
- Redis password
- server ports

Important config files:

- [config/autoload/redis.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/redis.php)
- [config/autoload/server.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/server.php)

## 8. Install and start Redis

If Redis is not already running:

```bash
sudo apt install -y redis-server
sudo service redis-server start
redis-cli ping
```

Expected:

```text
PONG
```

## 9. Start Hyperf

Foreground:

```bash
php bin/hyperf.php start
```

Background:

```bash
php bin/hyperf.php start -d
```

## 10. Verify PHP runtime

```bash
php -v
php -m | grep -E "swoole|msgpack|redis"
php -r "var_dump(function_exists('msgpack_pack'));"
```

## 11. Verify HTTP and WebSocket entry

Default HTTP entry:

```text
http://127.0.0.1:9501/login
```

Default WebSocket entry:

```text
ws://127.0.0.1:9502/
```

Actual ports depend on:

- [config/autoload/server.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/autoload/server.php)

## 12. Recommended manual verification flow

Use three accounts:

- `alice`
- `bob`
- `carol`

Run this order:

1. Login
2. Create room
3. Join room
4. Auto start
5. Call landlord
6. Play cards
7. Settlement
8. Room snapshot recovery
9. Chat message

## 13. Important commands to inspect protocol flow

Current protocol constants are defined in:

- [app/Constants/SubCmd.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Constants/SubCmd.php)

Current WebSocket routing is defined in:

- [config/ws-routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/ws-routes.php)

Current WebSocket entry is:

- [app/Controller/WebSocket/GameWebSocketController.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Controller/WebSocket/GameWebSocketController.php)

## 14. Common problems

### `msgpack_pack()` is undefined

Cause:

- `ext-msgpack` is not installed or not enabled

Check:

```bash
php -r "var_dump(function_exists('msgpack_pack'));"
```

### `The ext-swoole is required`

Cause:

- `ext-swoole` is not installed or not enabled

Check:

```bash
php -m | grep swoole
```

### WebSocket connects but no business response

Check these files first:

- [app/Gateway/WebSocket/PacketCodec.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Gateway/WebSocket/PacketCodec.php)
- [app/Application/Gateway/WsRouter.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Application/Gateway/WsRouter.php)
- [config/ws-routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/ws-routes.php)

### HTTP login page cannot open

Check:

- [config/routes.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/config/routes.php)
- [app/Controller/IndexController.php](E:/flyenv/PhpWebStudy-Data/server/php/hyperf/game-ddz/hyperf-skeleton/app/Controller/IndexController.php)

## 15. Final note

Before claiming the environment is ready, make sure all of the following are true:

- `php bin/hyperf.php start` runs successfully
- `/login` is accessible
- WebSocket handshake succeeds
- three-player game flow can be completed manually
- `msgpack` and `swoole` are both enabled
