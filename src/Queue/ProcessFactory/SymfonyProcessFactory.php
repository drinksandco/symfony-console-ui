<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue\ProcessFactory;

use Symfony\Component\Process\Process;

class SymfonyProcessFactory implements ProcessFactory
{
    public function create(array $command, string $cwd): Process
    {
        $process = new Process(['./bin/console', ...$command], $cwd);
        $process->setPty(true);

        return $process;
    }
}
