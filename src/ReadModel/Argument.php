<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\ReadModel;

class Argument
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $defaultValue,
        public readonly ?string $Value,
    ) {
    }
}
