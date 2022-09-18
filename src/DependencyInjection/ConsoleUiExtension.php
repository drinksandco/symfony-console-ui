<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class ConsoleUiExtension extends ConfigurableExtension
{
    /** @param array<mixed> $mergedConfig */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
    }
}
