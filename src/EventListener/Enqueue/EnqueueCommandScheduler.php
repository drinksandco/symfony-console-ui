<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener\Enqueue;

use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Drinksco\ConsoleUiBundle\EventListener\CommandSchedulerInterface;
use Enqueue\Client\ProducerInterface;

class EnqueueCommandScheduler implements CommandSchedulerInterface
{
    public function __construct(
        private readonly ProducerInterface $producer
    ) {
    }

    public function enqueue(CommandScheduled $event): void
    {
        $this->producer->sendCommand('run_command', $event->command);
    }
}
