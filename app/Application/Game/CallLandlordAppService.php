<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\CallLandlordService;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\MessagePusher;
use Swoole\WebSocket\Server;

final class CallLandlordAppService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly CallLandlordService $callLandlordService,
        private readonly ConnectionManager $connectionManager,
        private readonly MessagePusher $pusher,
    ) {
    }

    public function execute(Server $server, string $account, int $action): void
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4301);
        }

        $room = $this->callLandlordService->execute($room, $account, $action);
        $this->roomRepository->save($room);

        foreach ($room->players as $player) {
            $fd = $this->connectionManager->getFdByAccount($player->account);
            if ($fd === null) {
                continue;
            }

            $this->pusher->push(
                $server,
                $fd,
                MainCmd::CMD_GAME,
                SubCmd::SUB_GAME_CALL_RESP,
                [
                    'status' => 'success',
                    'code' => 0,
                    'message' => 'call result',
                    'data' => [
                        'room_id' => $room->roomId,
                        'account' => $account,
                        'chair_id' => $room->getPlayer($account)->chairId,
                        'action' => $action,
                        'current_chair_id' => $room->currentChairId,
                        'room_status' => $room->status->value,
                        'landlord' => $room->landlord,
                    ],
                ],
            );
        }

        if ($room->landlord !== null) {
            foreach ($room->players as $player) {
                $fd = $this->connectionManager->getFdByAccount($player->account);
                if ($fd === null) {
                    continue;
                }

                $this->pusher->push(
                    $server,
                    $fd,
                    MainCmd::CMD_GAME,
                    SubCmd::SUB_GAME_CATCH_BASECARD_RESP,
                    [
                        'status' => 'success',
                        'code' => 0,
                        'message' => 'landlord confirmed',
                        'data' => [
                            'room_id' => $room->roomId,
                            'landlord' => $room->landlord,
                            'chair_id' => $room->getPlayer($room->landlord)->chairId,
                            'base_cards' => $room->baseCards,
                            'current_chair_id' => $room->currentChairId,
                            'room_status' => $room->status->value,
                            'my_cards' => $player->cards,
                        ],
                    ],
                );
            }
        }
    }
}
