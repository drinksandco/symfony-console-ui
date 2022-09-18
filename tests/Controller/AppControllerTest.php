<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\Controller\AppController;
use Drinksco\ConsoleUiBundle\ReadModel\Argument;
use Drinksco\ConsoleUiBundle\ReadModel\Command;
use Drinksco\ConsoleUiBundle\ReadModel\Option;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class AppControllerTest extends TestCase
{
    /** @dataProvider getExpectedCommands */
    public function testRenderConsoleUiTemplate(array $commands): void
    {
        $environment = $this->createMock(Environment::class);
        $environment->expects($this->once())
            ->method('render')
            ->with('@ConsoleUi/console-ui.html.twig', [
                'commands' => $commands,
                'menu_items' => ['root'],
            ]);

        $controller = new AppController(
            $this->createMock(KernelInterface::class),
            $environment
        );

        $request = new Request();

        $response = $controller->__invoke($request);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testThrowNotFoundExceptionWithUnrecognizedConsoleNamespace(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $environment = $this->createMock(Environment::class);

        $controller = new AppController(
            $this->createMock(KernelInterface::class),
            $environment
        );

        $request = new Request([], [], ['namespace' => 'foo']);

        $controller->__invoke($request);
    }

    public function getExpectedCommands(): Generator
    {
        yield 'Get root namespace commands' => [
            [
                new Command(
                    "help",
                    "Display help for a command",
                    "help",
                    [
                        new Argument(
                            "command_name",
                            "The command name",
                            "help",
                            "help",
                        )
                    ],
                    [
                        new Option(
                            "format",
                            "The output format (txt, xml, json, or md)",
                            true,
                            "txt",
                            "txt",
                        ),
                        new Option(
                            "raw",
                            "To output raw command help",
                            false,
                            "",
                            "",
                        )
                    ]
                ),
                new Command(
                    "list",
                    "List commands",
                    "list",
                    [
                        new Argument(
                            "namespace",
                            "The namespace name",
                            "",
                            "",
                        )
                    ],
                    [
                        new Option(
                            "raw",
                            "To output raw command list",
                            false,
                            "",
                            "",
                        ),
                        new Option(
                            "format",
                            "The output format (txt, xml, json, or md)",
                            true,
                            "txt",
                            "txt",
                        ),
                        new Option(
                            "short",
                            "To skip describing commands' arguments",
                            false,
                            "",
                            "",
                        )
                    ]
                ),
                new Command(
                    "_complete",
                    "Internal command to provide shell completion suggestions",
                    "_complete",
                    [],
                    [
                        new Option(
                            "shell",
                            "The shell type (\"bash\", \"fish\")",
                            true,
                            "",
                            "",
                        ),
                        new Option(
                            "input",
                            "An array of input tokens (e.g. COMP_WORDS or argv)",
                            true,
                            null,
                            null,
                        ),
                        new Option(
                            "current",
                            "The index of the \"input\" array that the cursor is in (e.g. COMP_CWORD)",
                            true,
                            "",
                            "",
                        ),
                        new Option(
                            "symfony",
                            "The version of the completion script",
                            true,
                            "",
                            "",
                        )
                    ]
                ),
                new Command(
                    "completion",
                    "Dump the shell completion script",
                    "completion",
                    [
                        new Argument(
                            "shell",
                            'The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given',
                            "",
                            "",
                        )
                    ],
                    [
                        new Option(
                            "debug",
                            "Tail the completion debug log",
                            false,
                            "",
                            "",
                        )
                    ]
                )
            ],
        ];
    }
}
