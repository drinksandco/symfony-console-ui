<?php

namespace Drinksco\ConsoleUiBundle\Finder\Symfony;

use Drinksco\ConsoleUiBundle\Finder\CommandFinder;
use Drinksco\ConsoleUiBundle\ReadModel\Argument;
use Drinksco\ConsoleUiBundle\ReadModel\Command as ReadModelCommand;
use Drinksco\ConsoleUiBundle\ReadModel\Option;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

class SymfonyKernelApplicationFinder implements CommandFinder
{
    public function __construct(
        private readonly Application $application
    ) {
    }

    /** @return array<ReadModelCommand> */
    public function findByNamespace(?string $namespace): array
    {
        $commands = $this->application->all($namespace);

        if ('root' === $namespace) {
            $commands = $this->getNoNamespacedCommands();
        }

        return $this->hydrateCommands($commands);
    }

    /** @return array<Command> */
    private function getNoNamespacedCommands(): array
    {
        $commands = [];
        $allCommands = $this->application->all();
        foreach ($allCommands as $name => $command) {
            Assert::string($name);
            if (str_contains($name, ':') || str_starts_with($name, '_')) {
                continue;
            }

            $commands[] = $command;
        }

        return $commands;
    }

    /**
     * @param array<Command> $commands
     * @return array<ReadModelCommand>
     */
    private function hydrateCommands(array $commands): array
    {
        return array_values(array_map(function (Command $command): ReadModelCommand {
            $name = $command->getName();
            Assert::string(
                $name,
                sprintf('Command od class "%s" without name not expected at this point.', $command::class)
            );

            return new ReadModelCommand(
                $name,
                $command->getDescription(),
                $name,
                $this->hydrateArguments($command->getDefinition()->getArguments()),
                $this->hydrateOptions($command->getDefinition()->getOptions()),
            );
        }, $commands));
    }

    /**
     * @param array<InputArgument> $arguments
     * @return array<Argument>
     */
    private function hydrateArguments(array $arguments): array
    {
        return array_values(array_map(function (InputArgument $argument): Argument {
            // @TODO prepare to other types of default values
            /** @var string $defaultValue */
            $defaultValue = is_string($argument->getDefault()) ? $argument->getDefault() : null;
            return new Argument(
                $argument->getName(),
                $argument->getDescription(),
                $defaultValue,
                $defaultValue,
            );
        }, $arguments));
    }

    /**
     * @param array<InputOption> $options
     * @return array<Option>
     */
    private function hydrateOptions(array $options): array
    {
        return array_values(array_map(function (InputOption $option): Option {
            // @TODO prepare to other types of default values
            /** @var string $defaultValue */
            $defaultValue = is_string($option->getDefault()) ? $option->getDefault() : null;
            return new Option(
                $option->getName(),
                $option->getDescription(),
                $option->acceptValue(),
                $defaultValue,
                $defaultValue,
            );
        }, $options));
    }

    public function findAllNamespaces(): array
    {
        $namespaces = [];
        $commands = $this->application->all();
        $rawNamespaces = array_keys($commands);
        foreach ($rawNamespaces as $rawNamespace) {
            Assert::string($rawNamespace);
            $command = $commands[$rawNamespace];
            if (in_array($rawNamespace, $command->getAliases())) {
                continue;
            }

            if (str_contains($rawNamespace, ':')) {
                $namespaces[explode(':', $rawNamespace)[0]] = null;
            }
        }

        $namespaces = array_keys($namespaces);
        sort($namespaces);

        return array_merge(['root'], $namespaces);
    }
}
