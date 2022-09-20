<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Event;

class CommandStarted
{
    public function __construct(
        public readonly string $status,
        public readonly string $command,
        public readonly ?int $processId
    ) {
    }
}
