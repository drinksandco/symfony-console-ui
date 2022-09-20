<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Queue\ProcessFactory;

use PHPUnit\Framework\TestCase;

class SymfonyProcessFactoryTest extends TestCase
{
    public function testCreateProcessInstancesConfiguredForSymfonyConsole(): void
    {
        $factory = new SymfonyProcessFactory();

        $process = $factory->create(['help', 'help', '--format=txt'], '.');
        $this->assertTrue($process->isPty());
        $this->assertSame("'./bin/console' 'help' 'help' '--format=txt'", $process->getCommandLine());
    }
}
