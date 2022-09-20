<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener\Mercure;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\EventListener\CommandWatcher;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureCommandWatcher implements CommandWatcher
{
    public function __construct(
        private readonly HubInterface $hub,
    ) {
    }

    public function handleScheduledCommandReceived(ScheduledCommandReceived $event): void
    {
        $this->hub->publish(
            new Update(
                'http://example.com/' . $event->command,
                $this->parseMessage('STOPPED', 'bin/console ' . $event->commandLine . PHP_EOL)
            )
        );
    }

    public function handleCommandStarted(CommandStarted $event): void
    {
        $this->hub->publish(
            new Update(
                'http://example.com/' . $event->command,
                $this->parseMessage('RUNNING', '', $event->processId)
            )
        );
    }

    public function handleCommandFailed(CommandFailed $event): void
    {
        $this->hub->publish(
            new Update(
                'http://example.com/' . $event->command,
                $this->parseMessage('FAILED', $event->output, $event->processId)
            )
        );
    }

    public function handleCommandOutputReceived(CommandOutputReceived $event): void
    {
        $this->hub->publish(
            new Update(
                'http://example.com/' . $event->command,
                $this->parseMessage('RUNNING', $event->output, $event->processId)
            )
        );
    }

    public function handleCommandSucceeded(CommandSucceeded $event): void
    {
        $this->hub->publish(
            new Update(
                'http://example.com/' . $event->command,
                $this->parseMessage('SUCCEEDED', $event->exitCode, $event->processId)
            )
        );
    }

    private function parseMessage(string $commandStatus, string $content, ?int $pid = null): string
    {
        return json_encode([
            'status' => $commandStatus,
            'content' => $content,
            'pid' => $pid
        ], JSON_THROW_ON_ERROR);
    }
}
