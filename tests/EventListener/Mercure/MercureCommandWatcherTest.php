<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\EventListener\Mercure;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureCommandWatcherTest extends TestCase
{
    public function testHandleScheduledCommandReceived(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with(new Update(
                    'http://example.com/help',
                    json_encode([
                        'status' => 'STOPPED',
                        'content' => 'bin/console help --format=txt' . PHP_EOL,
                        'pid' => null
                    ], JSON_THROW_ON_ERROR)
                )
            );
        $commandWatcher = new MercureCommandWatcher($hub);

        $commandWatcher->handleScheduledCommandReceived(new ScheduledCommandReceived(
            'STOPPED',
            'help',
            'help --format=txt'
        ));
    }

    public function testHandleCommandStarted(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with(new Update(
                    'http://example.com/help',
                    json_encode([
                        'status' => 'RUNNING',
                        'content' => '',
                        'pid' => 3453
                    ], JSON_THROW_ON_ERROR)
                )
            );
        $commandWatcher = new MercureCommandWatcher($hub);

        $commandWatcher->handleCommandStarted(new CommandStarted(
            'RUNNING',
            'help',
            3453
        ));
    }

    public function testHandleCommandOutputReceived(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with(new Update(
                    'http://example.com/help',
                    json_encode([
                        'status' => 'RUNNING',
                        'content' => 'hola',
                        'pid' => 3458
                    ], JSON_THROW_ON_ERROR)
                )
            );
        $commandWatcher = new MercureCommandWatcher($hub);

        $commandWatcher->handleCommandOutputReceived(new CommandOutputReceived(
            'RUNNING',
            'help',
            'hola',
            3458
        ));
    }

    public function testHandleCommandSucceeded(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with(new Update(
                    'http://example.com/help',
                    json_encode([
                        'status' => 'SUCCEEDED',
                        'content' => '0',
                        'pid' => 3438
                    ], JSON_THROW_ON_ERROR)
                )
            );
        $commandWatcher = new MercureCommandWatcher($hub);

        $commandWatcher->handleCommandSucceeded(new CommandSucceeded(
            'SUCCEEDED',
            'help',
            '0',
            3438
        ));
    }

    public function testHandleCommandFailed(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with(new Update(
                    'http://example.com/help',
                    json_encode([
                        'status' => 'FAILED',
                        'content' => 'ERROR',
                        'pid' => 3768
                    ], JSON_THROW_ON_ERROR)
                )
            );
        $commandWatcher = new MercureCommandWatcher($hub);

        $commandWatcher->handleCommandFailed(new CommandFailed(
            'FAILED',
            'help',
            'ERROR',
            '127',
            3768
        ));
    }
}
