<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Event;

class CommandFailed
{
    public function __construct(
        public readonly string $status,
        public readonly string $command,
        public readonly string $output,
        public readonly string $exitCode,
        public readonly ?int $processId
    ) {
    }
}
