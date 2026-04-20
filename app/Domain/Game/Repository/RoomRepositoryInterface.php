<?php
declare(strict_types=1);

namespace App\Domain\Game\Repository;

use App\Domain\Game\Entity\Room;

interface RoomRepositoryInterface
{
    public function save(Room $room): void;

    public function find(string $roomId): ?Room;

    public function findByAccount(string $account): ?Room;

    public function bindAccountToRoom(string $account, string $roomId): void;

    public function removeAccountRoomBinding(string $account): void;
}