<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\Controller\AppController;
use Drinksco\ConsoleUiBundle\Controller\CommandScheduleController;
use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\EventListener\CommandWatcher;
use Drinksco\ConsoleUiBundle\EventListener\Mercure\MercureCommandWatcher;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\SymfonyProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\Enqueue\RunCommandProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Webmozart\Assert\Assert;

class ConsoleUiExtension extends ConfigurableExtension
{
    /** @param array<mixed> $mergedConfig */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $this->configureControllers($container);
        $this->configureCommandProcessor($container);
        $this->configureCommandHandler($container);
        $this->configureCommandWatcher($container);
    }

    private function configureControllers(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(AppController::class, AppController::class)
            ->addArgument(new Reference('kernel'))
            ->addArgument(new Reference('twig'))
            ->setAutoconfigured(true)
            ->addTag('controller.service_arguments');
        $containerBuilder->register(CommandScheduleController::class, CommandScheduleController::class)
            ->addArgument(new Reference(QueueCommandHandler::class))
            ->setAutoconfigured(true)
            ->addTag('controller.service_arguments');
    }

    private function configureCommandProcessor(ContainerBuilder $containerBuilder): void
    {
        $configuration = $this->getConfiguration([], $containerBuilder);
        Assert::notNull($configuration);
        $config = $this->processConfiguration($configuration, []);
        $containerBuilder->register(ProcessFactory::class, SymfonyProcessFactory::class);

        if ('enqueue_php' === $config['command_provider']) {
            $containerBuilder->register(RunCommandProcessor::class, RunCommandProcessor::class)
                ->addArgument(new Reference('event_dispatcher'))
                ->addArgument(new Reference(ProcessFactory::class))
                ->addArgument('%kernel.project_dir%')
                ->setAutoconfigured(true)
                ->addTag(
                    'enqueue.client.processor',
                    ['name' => 'enqueue.client.processor', 'topicName' => 'run_command'],
                );
        }
    }

    private function configureCommandHandler(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(QueueCommandHandler::class, QueueCommandHandler::class)
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument('%env(APP_ENV)%');
    }

    private function configureCommandWatcher(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(CommandWatcher::class, MercureCommandWatcher::class)
            ->addArgument(new Reference('mercure.hub.default'))
            ->addTag('kernel.event_listener', [
                'event' => CommandStarted::class,
                'method' => 'handleCommandStarted',
            ])
            ->addTag('kernel.event_listener', [
                'event' => ScheduledCommandReceived::class,
                'method' => 'handleScheduledCommandReceived',
            ])
            ->addTag('kernel.event_listener', [
                'event' => CommandOutputReceived::class,
                'method' => 'handleCommandOutputReceived',
            ])
            ->addTag('kernel.event_listener', [
                'event' => CommandSucceeded::class,
                'method' => 'handleCommandSucceeded',
            ])
            ->addTag('kernel.event_listener', [
                'event' => CommandFailed::class,
                'method' => 'handleCommandFailed',
            ]);
    }
}
