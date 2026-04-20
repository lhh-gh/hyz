# Final Checklist

## Current status

The Hyperf 3 skeleton now includes the core DDZ websocket flow:

- websocket entry/controller
- packet codec and router
- connection manager
- create room
- join room
- auto start on 3 players
- deal cards
- call landlord
- play/pass cards
- settlement broadcast
- room snapshot recovery
- room chat

## Still required before production use

- install and enable `ext-swoole`
- install and enable `ext-msgpack`
- run PHPUnit and fix any runtime issues
- run end-to-end websocket verification with 3 clients
- verify frontend field compatibility against new payloads
- normalize all remaining user-facing messages
- remove or archive unused placeholder directories if desired

## Recommended manual verification

1. Open 3 websocket clients with different accounts.
2. Create a room with client A.
3. Join with clients B and C.
4. Verify auto-start and per-player `my_cards`.
5. Complete call-landlord flow.
6. Play several rounds, including a pass action.
7. Verify settlement broadcast on finish.
8. Disconnect one client and request room snapshot after reconnect.
9. Send a room chat message and verify room-only broadcast.

## Low-risk legacy files to keep for now

- `app/Game/Core/Packet.php`
- `app/Game/Core/DdzPoker.php`
- `app/Game/Core/JokerPoker.php`
- `app/Controller/IndexController.php`

## Can be cleaned later after full runtime verification

- placeholder empty directories under `app/Application/*`
- unused helper constants if not referenced
- extra listeners/models not needed by your deployment
