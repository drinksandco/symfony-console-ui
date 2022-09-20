<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\DependencyInjection;

use Drinksco\ConsoleUiBundle\DependencyInjection\ConsoleUiExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TestContainerFactory
{
    private const DEFAULT_CONFIG = [
        'command_provider' => 'enqueue_php',
        'provider_options' => [
            'enqueue_php' => [
                'queue_name' => 'default_queue',
            ]
        ],
    ];

    public static function create(CompilerPassInterface $compilerPass): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        self::setDependencies($containerBuilder);

        $extension = new ConsoleUiExtension();
        $containerBuilder->registerExtension($extension);
        $containerBuilder->loadFromExtension('console_ui', self::DEFAULT_CONFIG);
        $extension->loadInternal(self::DEFAULT_CONFIG, $containerBuilder);

        $compilerPass->process($containerBuilder);

        return $containerBuilder;
    }

    private static function setDependencies(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('environment', 'test');
        $containerBuilder->setParameter('debug', false);
        $containerBuilder->register('kernel', TestKernel::class)
            ->addArgument('%environment%')
            ->addArgument('%debug%');
        $containerBuilder->register(ArrayLoader::class, ArrayLoader::class);
        $containerBuilder->register('twig', Environment::class)
            ->addArgument(new Reference(ArrayLoader::class));

        $containerBuilder->register('event_dispatcher', EventDispatcherInterface::class)
            ->setClass(EventDispatcher::class);

        $containerBuilder->register('enqueue.client.default_queue.lazy_producer', TestProducer::class);
    }
}
