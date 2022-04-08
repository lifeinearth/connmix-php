<?php

namespace Connmix;

interface NodeInterface
{

    public function send(string $method, array $params = []): MessageInterface;

    public function meshSend(int $clientId, string $data): MessageInterface;

    public function meshPublish(string $channel, string $data): MessageInterface;

    public function close(): void;

}
