<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener\Enqueue;

use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Enqueue\Client\ProducerInterface;
use PHPUnit\Framework\TestCase;

class EnqueueCommandSchedulerTest extends TestCase
{
    public function testAddCommandToQueueJobProducerUsingEnqueuePHPLibrary(): void
    {
        $event = new CommandScheduled('help help --format=txt --env=test', null);

        $producer = $this->createMock(ProducerInterface::class);
        $producer->expects($this->once())
            ->method('sendCommand')
            ->with('run_command', 'help help --format=txt --env=test');

        $scheduler = new EnqueueCommandScheduler($producer);
        $scheduler->enqueue($event);
    }
}
