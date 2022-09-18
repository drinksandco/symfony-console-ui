<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class QueueCommandHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $env
    ) {
    }

    public function handle(QueuedCommand $command): void
    {
        $argumentString = $this->createArgumentString($command->arguments);
        $optionsString = $this->createArgumentString($command->options);
        $fullCommand = sprintf('%s %s%s--env=%s', $command->name, $argumentString, $optionsString, $this->env);
        $this->eventDispatcher->dispatch(new CommandScheduled($fullCommand));
    }

    /** @param array<mixed> $arguments */
    private function createArgumentString(array $arguments): string
    {
        if ([] === $arguments) {
            return '';
        }

        $optionList = [];
        foreach ($arguments as $value) {
            Assert::string($value);
            $optionList[] = sprintf('%s', $value);
        }

        return implode(' ', $optionList) . ' ';
    }
}
