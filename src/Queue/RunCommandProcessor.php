<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\Result;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunCommandProcessor implements Processor, CommandSubscriberInterface
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly string $projectDir,
    ) {
    }

    public function process(Message $message, Context $context): string|object
    {
        $commandline = $message->getBody();

        $command = explode(' ', $commandline);

        $this->hub->publish(
            new Update(
                'http://example.com/' . $command[0],
                $this->parseMessage('STOPPED', 'bin/console '. $commandline . PHP_EOL)
            )
        );
        $process = new Process(['./bin/console', ...$command], $this->projectDir);
        $process->setPty(true);

        try {
            $process->start();
            while ($process->isRunning()) {
                $this->hub->publish(
                    new Update(
                        'http://example.com/' . $command[0],
                        $this->parseMessage('RUNNING', $process->getIncrementalOutput())
                    )
                );
            }

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $this->hub->publish(
                new Update(
                    'http://example.com/' . $command[0],
                    $this->parseMessage('RUNNING', $process->getIncrementalOutput())
                )
            );

            $this->hub->publish(
                new Update(
                    'http://example.com/' . $command[0],
                    $this->parseMessage('SUCCEED', $process->getStatus())
                )
            );

            return Result::ACK;
        } catch (ProcessFailedException $e) {
            $this->hub->publish(
                new Update(
                    'http://example.com/' . $command[0],
                    $this->parseMessage('FAILED', $process->getErrorOutput())
                )
            );

            return Result::reject(sprintf(
                'The process failed with exception: "%s" in %s at %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }
    }

    private function parseMessage(string $commandStatus, string $content): string
    {
        return json_encode([
            'status' => $commandStatus,
            'content' => $content,
        ], JSON_THROW_ON_ERROR);
    }

    public static function getSubscribedCommand(): string
    {
        return 'run_command';
    }
}
