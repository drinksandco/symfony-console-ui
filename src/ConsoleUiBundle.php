<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle;

use Drinksco\ConsoleUiBundle\DependencyInjection\ConsoleUiExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConsoleUiBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ConsoleUiExtension();
    }
}