<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\QueuedCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommandScheduleController
{
    public function __construct(
        private readonly QueueCommandHandler $handler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $parsedBody = json_decode($request->getContent(), true);

        $command = new QueuedCommand(
            $parsedBody['name'],
            $parsedBody['arguments'],
            $parsedBody['options'],
        );
        $this->handler->handle($command);

        return  new JsonResponse([
            'status' => 'OK'
        ]);
    }
}
