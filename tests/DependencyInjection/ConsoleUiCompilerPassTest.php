<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\Controller\AppController;
use Drinksco\ConsoleUiBundle\Controller\CommandScheduleController;
use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Drinksco\ConsoleUiBundle\EventListener\CommandScheduler;
use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use PHPUnit\Framework\TestCase;
use Test\Drinksco\ConsoleUiBundle\DependencyInjection\TestContainerFactory;

class ConsoleUiCompilerPassTest extends TestCase
{
    public function testRegisterAppControllerInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition(AppController::class);
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());

        $service = $containerBuilder->get(AppController::class);
        $this->assertInstanceOf(AppController::class, $service);
    }

    public function testRegisterQueueCommandHandlerInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition(QueueCommandHandler::class);
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());

        $service = $containerBuilder->get(QueueCommandHandler::class);
        $this->assertInstanceOf(QueueCommandHandler::class, $service);
    }

    public function testRegisterCommandSchedulerControllerInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition(CommandScheduleController::class);
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());

        $service = $containerBuilder->get(CommandScheduleController::class);
        $this->assertInstanceOf(CommandScheduleController::class, $service);
    }

    public function testRegisterCommandSchedulerInTheContainer(): void
    {
        $containerBuilder = TestContainerFactory::create(new ConsoleUiCompilerPass());
        $definition = $containerBuilder->getDefinition(CommandScheduler::class);
        $this->assertFalse($definition->isAutowired());
        $this->assertFalse($definition->isLazy());
        $tags = $definition->getTags();
        $this->assertSame([
            "kernel.event_listener" => [
                [
                    "event" => CommandScheduled::class,
                    'method' => 'enqueue'
                ]
            ]
        ], $tags);

        $service = $containerBuilder->get(CommandScheduler::class);
        $this->assertInstanceOf(CommandScheduler::class, $service);
    }
}
