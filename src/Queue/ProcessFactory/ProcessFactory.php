<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue\ProcessFactory;

use Symfony\Component\Process\Process;

interface ProcessFactory
{
    /**  @param array<int, string> $command */
    public function create(array $command, string $cwd): Process;
}
