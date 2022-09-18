<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\ReadModel;

class Command
{
    /**
     * @param array<Argument> $arguments
     * @param array<Option> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $command,
        public readonly array $arguments,
        public readonly array $options,
    ) {
    }
}
