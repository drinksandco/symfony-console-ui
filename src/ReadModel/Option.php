<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\ReadModel;

class Option
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly bool $acceptValue,
        public readonly ?string $defaultValue,
        public readonly ?string $Value,
    ) {
    }
}
