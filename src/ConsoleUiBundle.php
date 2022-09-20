<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle;

use Drinksco\ConsoleUiBundle\DependencyInjection\ConsoleUiCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConsoleUiBundle extends Bundle
{
    private const DEFAULT_CONFIG = [
//        'command_provider' => 'enqueue-php',
//        'provider_options' => [
//            'enqueue-php' => [
//                'queue_name' => 'default_queue',
//            ]
//        ],
    ];

    public function build(ContainerBuilder $container): void
    {
        $container->loadFromExtension('console_ui', self::DEFAULT_CONFIG);
        $container->addCompilerPass(new ConsoleUiCompilerPass());
    }
}
