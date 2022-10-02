<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\EventListener\CommandWatcher;
use Drinksco\ConsoleUiBundle\EventListener\Mercure\MercureCommandWatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Test\Drinksco\ConsoleUiBundle\DependencyInjection\TestContainerFactory;

class ConsoleUiExtensionTest extends TestCase
{
    public function testRegisterTheMercureHubInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition('console_ui.hub.default');
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());

        $service = $containerBuilder->get('console_ui.hub.default');
        $this->assertInstanceOf(HubInterface::class, $service);
    }

    public function testRegisterCommandWatcherInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition(CommandWatcher::class);
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());

        $service = $containerBuilder->get(CommandWatcher::class);
        $this->assertInstanceOf(MercureCommandWatcher::class, $service);
    }
}
