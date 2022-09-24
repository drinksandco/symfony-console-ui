<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Finder;

use Drinksco\ConsoleUiBundle\ReadModel\Command;

interface CommandFinder
{
    /** @return array<Command> */
    public function findByNamespace(?string $namespace): array;

    /** @return array<string> */
    public function findAllNamespaces(): array;
}
