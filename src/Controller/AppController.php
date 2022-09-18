<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class AppController
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Environment     $template,
    )
    {
    }

    public function __invoke(Request $request): Response
    {
        $namespace = $request->get('namespace');

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
     * @return array<array{name: string, }>
     */
    private function serializeCommands(array $all): array
    {
        return array_values(array_map(function (Command $command) {
            return [
                'name' => $command->getName(),
                'command' => $command->getName(),
                'description' => $command->getDescription(),
                'arguments' => $this->serializeArguments($command->getDefinition()->getArguments()),
                'options' => $this->serializeOptions($command->getDefinition()->getOptions()),
            ];
        }, $all));
    }

    /**
     * @param array<string, InputArgument> $getArguments
     * @return array<array{name: string, type: string, vdefaultVlue: string}>
     */
    private function serializeArguments(array $getArguments): array
    {
        return array_values(array_map(function (InputArgument $argument) {
            $defaultValue = [] === $argument->getDefault() ? null : (string)$argument->getDefault();
            return [
                'name' => $argument->getName(),
                'description' => $argument->getDescription(),
                'defaultValue' => $defaultValue,
                'value' => $defaultValue,
            ];
        }, $getArguments));
    }

    /**
     * @param array<string, InputOption> $getOptions
     * @return array<array{name: string, type: string, vdefaultVlue: string}>
     */
    private function serializeOptions(array $getOptions): array
    {
        return array_values(array_map(function (InputOption $option) {
            $defaultValue = [] === $option->getDefault() ? null : (string)$option->getDefault();
            return [
                'name' => $option->getName(),
                'accept_value' => $option->acceptValue(),
                'description' => $option->getDescription(),
                'defaultValue' => $defaultValue,
                'value' => $defaultValue,
            ];
        }, $getOptions));
    }

    private function serializeMenuItems(Application $application): array
    {
        $namespaces = [];
        $commands = $application->all();
        $rawNamespaces = array_keys($commands);
        foreach ($rawNamespaces as $rawNamespace) {
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

    private function getNoNamespacedCommands(Application $application): array
    {
        $commands = [];
        $allCommands = $application->all();
        foreach ($allCommands as $name => $command) {
            if (str_contains($name, ':') || str_starts_with($name, '_')) {
                continue;
            }

            $commands[] = $command;
        }

        return $commands;
    }
}
