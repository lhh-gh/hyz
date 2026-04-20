<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Exception\BusinessException;
use App\Gateway\WebSocket\ConnectionManager;

final class ChatMessageService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly ConnectionManager $connectionManager,
    ) {
    }

    /**
     * @return array{room_id:string,account:string,content:string,ts:int,fds:int[]}
     */
    public function execute(string $account, string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new BusinessException('Chat content cannot be empty', 4402);
        }

        if (mb_strlen($content) > 200) {
            throw new BusinessException('Chat content is too long', 4403);
        }

        $room = $this->roomRepository->findByAccount($account);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4305);
        }

        $fds = [];
        foreach ($room->players as $player) {
            $fd = $this->connectionManager->getFdByAccount($player->account);
            if ($fd !== null) {
                $fds[] = $fd;
            }
        }

        return [
            'room_id' => $room->roomId,
            'account' => $account,
            'content' => $content,
            'ts' => time(),
            'fds' => $fds,
        ];
    }
}
