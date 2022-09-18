<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;
}
