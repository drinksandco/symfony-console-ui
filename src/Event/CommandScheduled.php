<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Event;

use DateTimeImmutable;

class CommandScheduled
{
    public function __construct(
        public readonly string $command
    ) {
    }
}
