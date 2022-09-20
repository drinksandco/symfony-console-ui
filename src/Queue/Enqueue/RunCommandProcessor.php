<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue\Enqueue;

use Drinksco\ConsoleUiBundle\Queue\ExecuteCommand;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\Result;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RunCommandProcessor implements Processor, CommandSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProcessFactory $processFactory,
        private readonly string $projectDir,
    ) {
    }

    public function process(Message $message, Context $context): string|object
    {
        $commandline = $message->getBody();

        try {
            ExecuteCommand::execute($this->processFactory, $this->eventDispatcher, $commandline, $this->projectDir);

            return Result::ACK;
        } catch (ProcessFailedException $e) {
            return Result::reject(sprintf(
                'The process failed with exception: "%s" in %s at %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }
    }

    public static function getSubscribedCommand(): string
    {
        return 'run_command';
    }
}
