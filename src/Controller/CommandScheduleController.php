<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Controller;

use Drinksco\ConsoleUiBundle\Queue\QueueCommandHandler;
use Drinksco\ConsoleUiBundle\Queue\QueuedCommand;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class CommandScheduleController
{
    public function __construct(
        private readonly QueueCommandHandler $handler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $parsedBody = json_decode($request->getContent(), true, 16, JSON_THROW_ON_ERROR);
            Assert::isArray($parsedBody);
            Assert::string($parsedBody['name']);
            Assert::isArray($parsedBody['arguments']);
            Assert::isArray($parsedBody['options']);
        } catch (JsonException $exception) {
            throw new BadRequestException('Invalid Json Request Given', 0, $exception);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestException($exception->getMessage(), 0, $exception);
        }

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
