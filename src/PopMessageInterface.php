<?php

namespace Connmix;

use Ratchet\RFC6455\Messaging\MessageInterface;

interface PopMessageInterface
{

    public function rawMessage(): MessageInterface;

    public function method(): string;

    public function clientId(): int;

    public function queue(): string;

    public function data(): array;

    public function id(): int;

}
