<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandScheduled;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\EventListener\CommandScheduler;
use Drinksco\ConsoleUiBundle\EventListener\Enqueue\EnqueueCommandScheduler;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\SymfonyProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\Enqueue\RunCommandProcessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Webmozart\Assert\Assert;

class ConsoleUiCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {

        $consoleUiConfig = $container->getExtensionConfig('console_ui');
        $mergedConfig = array_merge(...$consoleUiConfig);

        Assert::keyExists($mergedConfig, 'command_provider');

        $container->addCompilerPass(new AddEventAliasesPass([
            CommandScheduled::class => CommandScheduled::class,
            ScheduledCommandReceived::class => ScheduledCommandReceived::class,
            CommandStarted::class => CommandStarted::class,
            CommandOutputReceived::class => CommandOutputReceived::class,
            CommandSucceeded::class => CommandSucceeded::class,
            CommandFailed::class => CommandFailed::class,
        ]));

        $provider = $mergedConfig['command_provider'];
        Assert::string($provider);

        if ($provider === 'enqueue_php') {
            Assert::keyExists($mergedConfig, 'provider_options');
            Assert::isArray($mergedConfig['provider_options']);
            Assert::keyExists($mergedConfig['provider_options'], 'enqueue_php');
            Assert::isArray($mergedConfig['provider_options']['enqueue_php']);
            $providerOptions = $mergedConfig['provider_options']['enqueue_php'];
            Assert::keyExists($providerOptions, 'queue_name');
            Assert::string($providerOptions['queue_name']);
            $queueName = $providerOptions['queue_name'];

            $commandSchedulerDefinition = $container
                ->register(CommandScheduler::class, EnqueueCommandScheduler::class)
                ->addArgument(new Reference(sprintf('enqueue.client.%s.lazy_producer', $queueName)));

            $commandSchedulerDefinition->addTag('kernel.event_listener', [
                'event' => CommandScheduled::class,
                'method' => 'enqueue',
            ]);
        }
    }
}
