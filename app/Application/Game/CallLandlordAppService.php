<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\CallLandlordService;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\MessagePusher;
use App\Exception\BusinessException;
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
            throw new BusinessException('房间不存在', 4301);
        }

        $room = $this->callLandlordService->execute($room, $account, $action);
        $this->roomRepository->save($room);

        foreach (array_keys($room->players) as $playerAccount) {
            $fd = $this->connectionManager->getFdByAccount($playerAccount);
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
                    'room_id' => $room->roomId,
                    'current_chair_id' => $room->currentChairId,
                    'landlord' => $room->landlord,
                    'game_status' => $room->status->value,
                ]
            );
        }
    }
}