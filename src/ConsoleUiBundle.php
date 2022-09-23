<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle;

use Drinksco\ConsoleUiBundle\DependencyInjection\ConsoleUiCompilerPass;
use Drinksco\ConsoleUiBundle\DependencyInjection\ConsoleUiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConsoleUiBundle extends Bundle
{
    private const DEFAULT_CONFIG = [
        'command_provider' => 'enqueue_php',
        'provider_options' => [
            'enqueue_php' => [
                'queue_name' => 'console_queue',
            ]
        ],
    ];

    public function build(ContainerBuilder $container): void
    {
        $container->loadFromExtension('console_ui', self::DEFAULT_CONFIG);

        $container->prependExtensionConfig('enqueue', [
            'console_queue' => [
                'transport' => [
                    'dsn' => '%env(CONSOLE_QUEUE_DSN)%',
                ],
                'client' => [
                    'app_name' => 'console_ui%kernel.environment%',
                    'traceable_producer' => true,
                    'default_queue' => 'console_queue',
                    'router_queue' => 'console_queue',
                ]
            ],
        ]);

        $container->addCompilerPass(new ConsoleUiCompilerPass());
    }
}
