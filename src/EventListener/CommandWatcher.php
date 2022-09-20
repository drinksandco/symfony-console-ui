<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;

interface CommandWatcher
{
    public function handleScheduledCommandReceived(ScheduledCommandReceived $event): void;
    public function handleCommandStarted(CommandStarted $event): void;
    public function handleCommandFailed(CommandFailed $event): void;
    public function handleCommandOutputReceived(CommandOutputReceived $event): void;
    public function handleCommandSucceeded(CommandSucceeded $event): void;
}
