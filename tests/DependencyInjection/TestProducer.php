<?php

declare(strict_types=1);

namespace Test\Drinksco\ConsoleUiBundle\DependencyInjection;

use Enqueue\Client\ProducerInterface;
use Enqueue\Rpc\Promise;

class TestProducer implements ProducerInterface
{
    public function sendEvent(string $topic, $message): void
    {
    }

    public function sendCommand(string $command, $message, bool $needReply = false): ?Promise
    {
        return null;
    }
}
