<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Domain\Game\Entity\Room;
use App\Domain\Game\Repository\RoomRepositoryInterface;
use App\Domain\Game\Service\DealCardService;
use App\Exception\BusinessException;

final class StartGameService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly DealCardService $dealCardService,
    ) {
    }

    public function execute(string $roomId): Room
    {
        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw new BusinessException('Room does not exist', 4303);
        }

        $room = $this->dealCardService->execute($room);
        $this->roomRepository->save($room);

        return $room;
    }
}
