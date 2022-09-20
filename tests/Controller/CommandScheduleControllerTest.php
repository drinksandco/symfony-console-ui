<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\Controller\CommandScheduleController;
use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\QueuedCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class CommandScheduleControllerTest extends TestCase
{
    public function testHandleValidJasonRequest(): void
    {
        $command = new QueuedCommand('list', [], ['--format=txt']);
        $handler = $this->createMock(QueueCommandHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($command);
        $controller = new CommandScheduleController($handler);

        $request = new Request([], [], [], [], [], [
            'HTTP_Content-Type' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ],
            '{"name":"list","arguments":[],"options":["--format=txt"]}'
        );

        $response = $controller->__invoke($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testThrowBadRequestExceptionWithNonJsonRequest(): void
    {
        $this->expectException(BadRequestException::class);
        $handler = $this->createMock(QueueCommandHandler::class);
        $controller = new CommandScheduleController($handler);

        $request = new Request([], [], [], [], [], [
            'HTTP_Content-Type' => 'application/form-urlencoded',
            'HTTP_Accept' => 'application/json',
        ],
            'name=list,arguments[],options[]=--format=txt'
        );

        $controller->__invoke($request);
    }

    public function testThrowBadRequestExceptionWithInvalidParameters(): void
    {
        $this->expectException(BadRequestException::class);
        $handler = $this->createMock(QueueCommandHandler::class);
        $controller = new CommandScheduleController($handler);

        $request = new Request([], [], [], [], [], [
            'HTTP_Content-Type' => 'application/form-urlencoded',
            'HTTP_Accept' => 'application/json',
        ],
            '{"name":5,"arguments":[],"options":["--format=txt"]}'
        );

        $controller->__invoke($request);
    }
}
