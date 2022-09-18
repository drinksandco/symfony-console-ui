<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

class QueuedCommand
{
    /**
     * @param array<mixed> $arguments
     * @param array<mixed> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
        public readonly array $options,
    ) {
    }
}
