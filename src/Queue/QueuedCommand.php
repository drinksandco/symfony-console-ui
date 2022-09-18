<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

class QueuedCommand
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
        public readonly array $options,
    ) {
    }
}
