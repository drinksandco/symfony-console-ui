<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener;

use Drinksco\ConsoleUiBundle\Event\CommandScheduled;

interface CommandScheduler
{
    public function enqueue(CommandScheduled $event): void;
}
