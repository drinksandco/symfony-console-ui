<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\Queue;

use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\QueuedCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class QueueCommandHandlerTest extends TestCase
{
    public function testHandleQueuedCommand(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(new CommandScheduled('help help --format=txt --env=test'));

        $command = new QueuedCommand('help', ['help'], ['--format=txt']);
        $handler = new QueueCommandHandler($eventDispatcher,'test');

        $handler->handle($command);

    }
}
