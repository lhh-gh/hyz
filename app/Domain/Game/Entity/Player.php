<?php

declare(strict_types=1);

namespace App\Domain\Game\Entity;

final class Player
{
    public function __construct(
        public string $account,
        public int $chairId,
        public array $cards = [],
        public array $playedCards = [],
        public ?int $callAction = null,
        public bool $isLandlord = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'account' => $this->account,
            'chair_id' => $this->chairId,
            'cards' => $this->cards,
            'played_cards' => $this->playedCards,
            'call_action' => $this->callAction,
            'is_landlord' => $this->isLandlord,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            account: (string) $data['account'],
            chairId: (int) $data['chair_id'],
            cards: $data['cards'] ?? [],
            playedCards: $data['played_cards'] ?? [],
            callAction: $data['call_action'] ?? null,
            isLandlord: (bool) ($data['is_landlord'] ?? false),
        );
    }
}