<?php

namespace Connmix;

interface MessageInterface
{

    public function rawMessage(): \Ratchet\RFC6455\Messaging\MessageInterface;

    public function type(): string;

    public function method(): string;

    public function error(): ?array;

    public function params(): ?array;

    public function result(): ?array;

    public function id(): ?int;

    public function firstParam(): ParamInterface;

    public function firstResult(): ResultInterface;

}
