<?php

declare(strict_types=1);

namespace App\Domain\Game\Entity;

use App\Domain\Game\Enum\RoomStatus;
use DomainException;

final class Room
{
    /**
     * @param array<string, Player> $players
     * @param array<int, array<string, mixed>> $steps
     */
    public function __construct(
        public string $roomId,
        public RoomStatus $status = RoomStatus::Waiting,
        public array $players = [],
        public array $baseCards = [],
        public int $currentChairId = 1,
        public int $round = 0,
        public array $steps = [],
        public ?string $landlord = null,
        public ?array $lastPlayedCards = null,
        public ?int $lastPlayedChairId = null,
    ) {
    }

    public function addPlayer(Player $player): void
    {
        if (count($this->players) >= 3) {
            throw new DomainException('Room is full');
        }

        $this->players[$player->account] = $player;
        $this->status = count($this->players) === 3 ? RoomStatus::Ready : RoomStatus::Waiting;
    }

    public function getPlayer(string $account): Player
    {
        if (! isset($this->players[$account])) {
            throw new DomainException("Player {$account} not found");
        }

        return $this->players[$account];
    }

    public function toArray(): array
    {
        return [
            'room_id' => $this->roomId,
            'status' => $this->status->value,
            'players' => array_map(static fn (Player $player) => $player->toArray(), $this->players),
            'base_cards' => $this->baseCards,
            'current_chair_id' => $this->currentChairId,
            'round' => $this->round,
            'steps' => $this->steps,
            'landlord' => $this->landlord,
            'last_played_cards' => $this->lastPlayedCards,
            'last_played_chair_id' => $this->lastPlayedChairId,
        ];
    }

    public static function fromArray(array $data): self
    {
        $players = [];
        foreach (($data['players'] ?? []) as $item) {
            $player = Player::fromArray($item);
            $players[$player->account] = $player;
        }

        return new self(
            roomId: (string) $data['room_id'],
            status: RoomStatus::from((string) $data['status']),
            players: $players,
            baseCards: $data['base_cards'] ?? [],
            currentChairId: (int) ($data['current_chair_id'] ?? 1),
            round: (int) ($data['round'] ?? 0),
            steps: $data['steps'] ?? [],
            landlord: $data['landlord'] ?? null,
            lastPlayedCards: $data['last_played_cards'] ?? null,
            lastPlayedChairId: $data['last_played_chair_id'] ?? null,
        );
    }
}