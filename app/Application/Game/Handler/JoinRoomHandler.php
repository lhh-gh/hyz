<?php

declare(strict_types=1);

namespace App\Application\Game\Handler;

use App\Application\Game\StartGameService;
use App\Application\Gateway\WsHandlerInterface;
use App\Application\Room\JoinRoomService;
use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Domain\Game\Entity\Room;
use App\DTO\WsMessage;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class JoinRoomHandler implements WsHandlerInterface
{
    public function __construct(
        private readonly JoinRoomService $service,
        private readonly StartGameService $startGameService,
        private readonly ConnectionManager $connectionManager,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function handle(Server $server, WsMessage $message): void
    {
        $roomId = (string) ($message->data['room_id'] ?? '');
        $room = $this->service->execute((string) $message->account, $roomId);

        $this->broadcastRoomJoined($server, $room);

        if (count($room->players) === 3) {
            $this->broadcastGameStarted($server, $this->startGameService->execute($room->roomId));
        }
    }

    private function broadcastRoomJoined(Server $server, Room $room): void
    {
        $this->pusher->pushToMany(
            $server,
            $this->getRoomFds($room),
            MainCmd::CMD_SYS,
            SubCmd::ENTER_ROOM_SUCC_RESP,
            [
                'status' => 'success',
                'code' => 0,
                'message' => 'join success',
                'data' => [
                    'room_id' => $room->roomId,
                    'room_status' => $room->status->value,
                    'players' => array_map(
                        static fn ($player) => [
                            'account' => $player->account,
                            'chair_id' => $player->chairId,
                        ],
                        array_values($room->players),
                    ),
                ],
            ],
        );
    }

    private function broadcastGameStarted(Server $server, Room $room): void
    {
        foreach ($room->players as $player) {
            $fd = $this->connectionManager->getFdByAccount($player->account);
            if ($fd === null) {
                continue;
            }

            $this->pusher->push(
                $server,
                $fd,
                MainCmd::CMD_GAME,
                SubCmd::SUB_GAME_START_RESP,
                [
                    'status' => 'success',
                    'code' => 0,
                    'message' => 'game started',
                    'data' => [
                        'room_id' => $room->roomId,
                        'room_status' => $room->status->value,
                        'current_chair_id' => $room->currentChairId,
                        'my_cards' => $player->cards,
                        'base_card_count' => count($room->baseCards),
                        'players' => array_map(
                            static fn ($item) => [
                                'account' => $item->account,
                                'chair_id' => $item->chairId,
                                'card_count' => count($item->cards),
                            ],
                            array_values($room->players),
                        ),
                    ],
                ],
            );
        }
    }

    /**
     * @return int[]
     */
    private function getRoomFds(Room $room): array
    {
        $fds = [];

        foreach (array_keys($room->players) as $playerAccount) {
            $fd = $this->connectionManager->getFdByAccount((string) $playerAccount);
            if ($fd !== null) {
                $fds[] = $fd;
            }
        }

        return $fds;
    }
}
