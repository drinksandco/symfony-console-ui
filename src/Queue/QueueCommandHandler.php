<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue;

use Enqueue\Client\ProducerInterface;

class QueueCommandHandler
{
    public function __construct(
        private readonly ProducerInterface $producer,
        private readonly string $env
    ) {
    }

    public function handle(QueuedCommand $command): void
    {
        $argumentString = $this->createArgumentString($command->arguments);
        $optionsString = $this->createArgumentString($command->options);
        $fullCommand = sprintf('%s %s%s--env=%s', $command->name, $argumentString, $optionsString, $this->env);
        $this->producer->sendCommand('run_command', $fullCommand);
    }

    private function createArgumentString(array $arguments): string
    {
        if ([] === $arguments) {
            return '';
        }

        $optionList = [];
        foreach ($arguments as $key => $value) {
            if (!is_int($key)) {
                $optionList[] = sprintf('--%s=%s', $key, $value);
                continue;
            }
            $optionList[] = sprintf('%s', $value);
        }

        return implode(' ', $optionList) . ' ';
    }
}
