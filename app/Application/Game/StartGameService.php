<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\DealCardService;
use DomainException;

/**
 *  开局
 */
final class StartGameService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly DealCardService $dealCardService,
    ) {
    }

    public function execute(string $roomId)
    {
        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw new DomainException('房间不存在');
        }

        $room = $this->dealCardService->execute($room);
        $this->roomRepository->save($room);

        return $room;
    }
}