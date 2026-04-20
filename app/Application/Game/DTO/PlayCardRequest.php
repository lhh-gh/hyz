<?php

declare(strict_types=1);

namespace App\Application\Game\DTO;

final readonly class PlayCardRequest
{
    public function __construct(
        public string $account,
        public array $cards,
        public bool $pass,
    ) {
    }
}