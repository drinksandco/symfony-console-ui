<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\Finder\Symfony;

use Drinksco\ConsoleUiBundle\Finder\CommandFinder;
use Drinksco\ConsoleUiBundle\Finder\Symfony\SymfonyKernelApplicationFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

class SymfonyKernelApplicationFinderTest extends TestCase
{
    public function testItFindAllKernelApplicationConsoleCommands(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $application = new Application($kernel);
        $finder = new SymfonyKernelApplicationFinder($application);
        $this->assertInstanceOf(CommandFinder::class, $finder);
        $commands = $finder->findByNamespace('root');
        $this->assertCount(3, $commands);
    }
}
