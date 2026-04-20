<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Constants\MainCmd;
use App\Constants\SubCmd;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\PlayCardService;
use App\Domain\Game\Service\SettlementService;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;
use App\Gateway\WebSocket\MessagePusher;
use App\Infrastructure\Persistence\Redis\RedisKey;
use App\Infrastructure\Support\DistributedLocker;
use Hyperf\Contract\ConfigInterface;
use Swoole\WebSocket\Server;

final class PlayCardAppService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly PlayCardService $playCardService,
        private readonly SettlementService $settlementService,
        private readonly ConnectionManager $connectionManager,
        private readonly MessagePusher $pusher,
        private readonly DistributedLocker $locker,
        private readonly ConfigInterface $config,
    ) {
    }

    public function execute(Server $server, string $account, array $cards, bool $pass): void
    {
        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4302);
        }

        $lockTtl = (int) $this->config->get('game.lock.ttl', 5);

        $room = $this->locker->withLock(RedisKey::lockRoom($room->roomId), function () use ($account, $cards, $pass) {
            $room = $this->roomRepository->findByAccount($account);
            if ($room === null) {
                throw new BusinessException('Room does not exist', 4302);
            }

            $room = $this->playCardService->execute($room, $account, $cards, $pass);
            $this->roomRepository->save($room);

            return $room;
        }, $lockTtl);

        $actingPlayer = $room->getPlayer($account);

        foreach ($room->players as $player) {
            $fd = $this->connectionManager->getFdByAccount($player->account);
            if ($fd === null) {
                continue;
            }

            $this->pusher->push(
                $server,
                $fd,
                MainCmd::CMD_GAME,
                SubCmd::SUB_GAME_OUT_CARD_RESP,
                [
                    'status' => 'success',
                    'code' => 0,
                    'message' => $pass ? 'pass success' : 'play success',
                    'data' => [
                        'room_id' => $room->roomId,
                        'account' => $account,
                        'chair_id' => $actingPlayer->chairId,
                        'action' => $pass ? 'pass' : 'play',
                        'cards' => $pass ? [] : $cards,
                        'current_chair_id' => $room->currentChairId,
                        'last_played_chair_id' => $room->lastPlayedChairId,
                        'last_played_cards' => $room->lastPlayedCards,
                        'left_card_count' => count($actingPlayer->cards),
                        'room_status' => $room->status->value,
                        'is_game_over' => $room->status->value === 'finished',
                    ],
                ],
            );
        }

        if ($room->status->value === 'finished') {
            $settlement = $this->settlementService->build($room, $account);

            foreach ($room->players as $player) {
                $fd = $this->connectionManager->getFdByAccount($player->account);
                if ($fd === null) {
                    continue;
                }

                $this->pusher->push(
                    $server,
                    $fd,
                    MainCmd::CMD_GAME,
                    SubCmd::SUB_GAME_SETTLEMENT_RESP,
                    [
                        'status' => 'success',
                        'code' => 0,
                        'message' => 'game over',
                        'data' => [
                            'room_id' => $room->roomId,
                            ...$settlement,
                        ],
                    ],
                );
            }

            foreach ($room->players as $player) {
                $this->roomRepository->removeAccountRoomBinding($player->account);
            }
            $this->roomRepository->delete($room->roomId);
        }
    }
}
