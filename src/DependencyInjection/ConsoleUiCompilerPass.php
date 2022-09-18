<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\EventListener\CommandSchedulerInterface;
use Drinksco\ConsoleUiBundle\EventListener\Enqueue\EnqueueCommandScheduler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

class ConsoleUiCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config/console-ui')
        );
        $loader->load('services/console-ui.yaml');

        $consoleUiConfig = $container->getExtensionConfig('console_ui');
        $mergedConfig = array_merge(...$consoleUiConfig);
        Assert::keyExists($mergedConfig, 'command_provider');

        $provider = $mergedConfig['command_provider'];
        Assert::string($provider);

        if ($provider === 'enqueue-php') {
            Assert::keyExists($mergedConfig, 'provider_options');
            Assert::isArray($mergedConfig['provider_options']);
            $providerOptions = $mergedConfig['provider_options'];
            Assert::keyExists($providerOptions, 'queue_name');
            Assert::string($providerOptions['queue_name']);
            $queueName = $providerOptions['queue_name'];

            $container->register(CommandSchedulerInterface::class, EnqueueCommandScheduler::class)
                ->addArgument(new Reference(sprintf('enqueue.client.%s.lazy_producer', $queueName)));
        }
    }
}
