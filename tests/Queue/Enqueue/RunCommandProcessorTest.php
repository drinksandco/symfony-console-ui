<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\Queue\Enqueue;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\Queue\Enqueue\RunCommandProcessor;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Enqueue\Fs\FsMessage;
use Interop\Queue\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RunCommandProcessorTest extends TestCase
{
    public function testRunProcessBasedOnHandledMessage(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly(5))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ScheduledCommandReceived::class)],
                [$this->isInstanceOf(CommandStarted::class)],
                [$this->isInstanceOf(CommandOutputReceived::class)],
                [$this->isInstanceOf(CommandOutputReceived::class)],
                [$this->isInstanceOf(CommandSucceeded::class)],
            );

        $processor = new RunCommandProcessor(
            $eventDispatcher,
            $this->getSuccessfulProcessAwareFactory(),
            './'
        );

        $processor->process(
            new FsMessage('help help --env=dev'),
            $this->createMock(Context::class)
        );
    }

    public function testProcessFailedWhileRunningBasedOnHandledMessage(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ScheduledCommandReceived::class)],
                [$this->isInstanceOf(CommandStarted::class)],
                [$this->isInstanceOf(CommandOutputReceived::class)],
                [$this->isInstanceOf(CommandFailed::class)],
            );

        $processor = new RunCommandProcessor(
            $eventDispatcher,
            $this->getFailingProcessAwareFactory(),
            './'
        );

        $processor->process(
            new FsMessage('help help --env=dev'),
            $this->createMock(Context::class)
        );
    }

    private function getSuccessfulProcessAwareFactory(): ProcessFactory
    {
        $process = $this->createMock(Process::class);
        $process->expects($this->once())
            ->method('start');
        $process->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);
        $process->expects($this->exactly(3))
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                false
            );
        $process->expects($this->exactly(3))
            ->method('getIncrementalOutput')
            ->willReturnOnConsecutiveCalls(
                '',
                'test',
                'test_1'
            );
        $processFactory = $this->createMock(ProcessFactory::class);
        $processFactory->expects($this->once())
            ->method('create')
            ->willReturn($process);

        return $processFactory;
    }

    private function getFailingProcessAwareFactory(): ProcessFactory
    {
        $process = $this->createMock(Process::class);
        $process->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);
        $process->expects($this->exactly(3))
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                false
            );
        $process->expects($this->exactly(2))
            ->method('getIncrementalOutput')
            ->willReturnOnConsecutiveCalls(
                '',
                'test',
            );

        $processFactory = $this->createMock(ProcessFactory::class);
        $processFactory->expects($this->once())
            ->method('create')
            ->willReturn($process);

        return $processFactory;
    }
}
