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
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Support\DistributedLocker;
use Hyperf\Contract\ConfigInterface;
use Swoole\WebSocket\Server;

final class CallLandlordAppService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly CallLandlordService $callLandlordService,
        private readonly ConnectionManager $connectionManager,
        private readonly MessagePusher $pusher,
        private readonly DistributedLocker $locker,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(Server $server, string $account, int $action): void
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4301);
        }

        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        $room = $this->locker->withLock(RedisKey::lockRoom($room->roomId), function () use ($account, $action) {
            $room = $this->roomRepository->findByAccount($account);
            if ($room === null) {
                throw new BusinessException('Room does not exist', 4301);
            }

            $room = $this->callLandlordService->execute($room, $account, $action);
            $this->roomRepository->save($room);

            return $room;
        }, $lockTtl);

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
