<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExecuteCommand
{
    public static function execute(
        ProcessFactory $processFactory,
        EventDispatcherInterface $eventDispatcher,
        string $commandLine,
        string $projectDir
    ): void {
        $command = explode(' ', $commandLine);

        $eventDispatcher->dispatch(new ScheduledCommandReceived('STOPPED', $command[0], $commandLine));
        $process = $processFactory->create($command, $projectDir);

        try {
            $process->start();
            $eventDispatcher->dispatch(new CommandStarted('RUNNING', '', $process->getPid()));
            while ($process->isRunning()) {
                $incrementalOutput = $process->getIncrementalOutput();
                if ('' === $incrementalOutput) {
                    continue;
                }

                $eventDispatcher->dispatch(new CommandOutputReceived(
                    'RUNNING',
                    $command[0],
                    $incrementalOutput,
                    $process->getPid()
                ));
            }

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $eventDispatcher->dispatch(new CommandOutputReceived(
                'RUNNING',
                $command[0],
                $process->getIncrementalOutput(),
                $process->getPid()
            ));

            $eventDispatcher->dispatch(new CommandSucceeded(
                'SUCCEEDED',
                $command[0],
                $process->getStatus(),
                $process->getPid()
            ));
        } catch (ProcessFailedException $exception) {
            $eventDispatcher->dispatch(new CommandFailed(
                'FAILED',
                $command[0],
                $process->getErrorOutput(),
                $process->getStatus(),
                $process->getPid()
            ));

            throw $exception;
        }
    }
}
