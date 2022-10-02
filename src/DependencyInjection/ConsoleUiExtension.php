<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\Command\StartConsoleUiCommand;
use Drinksco\ConsoleUiBundle\Controller\AppController;
use Drinksco\ConsoleUiBundle\Controller\CommandScheduleController;
use Drinksco\ConsoleUiBundle\Event\CommandFailed;
use Drinksco\ConsoleUiBundle\Event\CommandOutputReceived;
use Drinksco\ConsoleUiBundle\Event\CommandStarted;
use Drinksco\ConsoleUiBundle\Event\CommandSucceeded;
use Drinksco\ConsoleUiBundle\Event\ScheduledCommandReceived;
use Drinksco\ConsoleUiBundle\EventListener\CommandWatcher;
use Drinksco\ConsoleUiBundle\EventListener\Mercure\MercureCommandWatcher;
use Drinksco\ConsoleUiBundle\Finder\CommandFinder;
use Drinksco\ConsoleUiBundle\Finder\Symfony\SymfonyKernelApplicationFinder;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\ProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\ProcessFactory\SymfonyProcessFactory;
use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\Enqueue\RunCommandProcessor;
use Symfony\Bundle\FrameworkBundle\Console\Application as KernelApplication;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Mercure\Debug\TraceableHub;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\Jwt\FactoryTokenProvider;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Webmozart\Assert\Assert;

class ConsoleUiExtension extends ConfigurableExtension
{
    /** @param array<mixed> $mergedConfig */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $this->configureStartConsoleCommand($container);
        $this->configureCommandFinder($container);
        $this->configureControllers($container);
        $this->configureHub($container);
        $this->configureCommandProcessor($container);
        $this->configureCommandHandler($container);
        $this->configureCommandWatcher($container);
    }

    private function configureCommandFinder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(Application::class, KernelApplication::class)
            ->addArgument(new Reference('kernel'));
        $containerBuilder->register(CommandFinder::class, SymfonyKernelApplicationFinder::class)
            ->addArgument(new Reference(Application::class));
    }

    private function configureControllers(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('env(CONSOLE_API_URL)', 'http://localhost:3000');
        $containerBuilder->register(AppController::class, AppController::class)
            ->addArgument(new Reference(CommandFinder::class))
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

        $provider = $config['command_provider'] ?? [];
        Assert::string($provider);
        if ('enqueue_php' === $provider) {
            Assert::keyExists($config, 'provider_options');
            Assert::isArray($config['provider_options']);
            Assert::keyExists($config['provider_options'], 'enqueue_php');
            Assert::isArray($config['provider_options']['enqueue_php']);
            $providerOptions = $config['provider_options']['enqueue_php'];
            Assert::keyExists($providerOptions, 'queue_name');
            Assert::string($providerOptions['queue_name']);
            $queueName = $providerOptions['queue_name'];
            $containerBuilder->register(RunCommandProcessor::class, RunCommandProcessor::class)
                ->addArgument(new Reference('event_dispatcher'))
                ->addArgument(new Reference(ProcessFactory::class))
                ->addArgument('%kernel.project_dir%')
                ->setAutoconfigured(true)
                ->addTag(
                    'enqueue.command_subscriber',
                    ['name' => 'enqueue.command_subscriber', 'topicName' => 'run_command', 'client' => $queueName],
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
            ->addArgument(new Reference('console_ui.hub.default'))
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

    private function configureStartConsoleCommand(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(StartConsoleUiCommand::class, StartConsoleUiCommand::class)
            ->addArgument('%kernel.project_dir%')
            ->addTag('console.command');
    }

    private function configureHub(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('env(CONSOLE_UI_JWT_SECRET)', '!ChangeThisMercureHubJWTSecretKey!');
        $containerBuilder->setParameter('env(CONSOLE_UI_MERCURE_URL)', 'http://localhost:3001/.well-known/mercure');
        $containerBuilder->setParameter(
            'env(CONSOLE_UI_MERCURE_PUBLIC_URL)',
            'http://localhost:3001/.well-known/mercure'
        );

        $containerBuilder->register('console_ui.hub.default.jwt.factory', LcobucciFactory::class)
            ->addArgument('%env(CONSOLE_UI_JWT_SECRET)%')
            ->addArgument('hmac.sha256')
            ->addArgument(null)
            ->addArgument('')
            ->addTag('mercure.jwt.factory');
        $containerBuilder->register('console_ui.hub.default.jwt.provider', FactoryTokenProvider::class)
            ->addArgument(new Reference('console_ui.hub.default.jwt.factory'))
            ->addArgument([])
            ->addArgument(['*'])
            ->addTag('mercure.jwt.factory');
        $containerBuilder->register('console_ui.hub.default', Hub::class)
            ->addArgument('%env(CONSOLE_UI_MERCURE_URL)%')
            ->addArgument(new Reference('console_ui.hub.default.jwt.provider'))
            ->addArgument(new Reference('console_ui.hub.default.jwt.factory'))
            ->addArgument('%env(CONSOLE_UI_MERCURE_PUBLIC_URL)%')
            ->addArgument((new Reference('http_client', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))
            ->addTag('mercure.hub');
    }
}
