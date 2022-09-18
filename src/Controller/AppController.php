<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\ReadModel\Argument;
use Drinksco\ConsoleUiBundle\ReadModel\Command as ReadModelCommand;
use Drinksco\ConsoleUiBundle\ReadModel\Option;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

class AppController
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Environment $template,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $namespace = $request->attributes->get('namespace');
        Assert::nullOrString($namespace);

        $application = new Application($this->kernel);
        $commands = $application->all($namespace);

        if ('root' === $namespace) {
            $commands = $this->getNoNamespacedCommands($application);
        }

        if ([] === $commands) {
            throw new NotFoundHttpException('Given Console namespace does not exist.');
        }

        return new Response($this->template->render('@ConsoleUi/console-ui.html.twig', [
            'commands' => $this->serializeCommands($commands),
            'menu_items' => $this->serializeMenuItems($application),
        ]));
    }

    /**
     * @param array<Command> $all
     * @return array<ReadModelCommand>
     */
    private function serializeCommands(array $all): array
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
                $this->serializeArguments($command->getDefinition()->getArguments()),
                $this->serializeOptions($command->getDefinition()->getOptions()),
            );
        }, $all));
    }

    /**
     * @param array<array-key, InputArgument> $getArguments
     * @return array<Argument>
     */
    private function serializeArguments(array $getArguments): array
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
        }, $getArguments));
    }

    /**
     * @param array<array-key, InputOption> $getOptions
     * @return array<Option>
     */
    private function serializeOptions(array $getOptions): array
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
        }, $getOptions));
    }

    /** @return array<string> */
    private function serializeMenuItems(Application $application): array
    {
        $namespaces = [];
        $commands = $application->all();
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

    /** @return array<Command> */
    private function getNoNamespacedCommands(Application $application): array
    {
        $commands = [];
        $allCommands = $application->all();
        foreach ($allCommands as $name => $command) {
            Assert::string($name);
            if (str_contains($name, ':') || str_starts_with($name, '_')) {
                continue;
            }

            $commands[] = $command;
        }

        return $commands;
    }
}
