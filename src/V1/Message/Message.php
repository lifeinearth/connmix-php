<?php

namespace Connmix\V1\Message;

use Connmix\MessageInterface;
use Connmix\ParamInterface;
use Connmix\ResultInterface;
use Ratchet\RFC6455\Messaging\MessageInterface as RatchetMessageInterface;

class Message implements MessageInterface
{

    /**
     * @var RatchetMessageInterface
     */
    protected $raw;

    /**
     * @var array
     */
    protected $storage;

    /**
     * @param RatchetMessageInterface $message
     */
    public function __construct(RatchetMessageInterface $message)
    {
        $this->raw = $message;
        $this->storage = json_decode($message->getPayload(), true) ?: [];
    }

    /**
     * @return RatchetMessageInterface
     */
    public function rawMessage(): RatchetMessageInterface
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        if ($this->method() == 'queue.pop') {
            return 'pop';
        }

        if (!is_null($this->error())) {
            return 'error';
        }

        if (!is_null($this->result())) {
            return 'result';
        }

        return 'unknown';
    }

    public function method(): string
    {
        return $this->storage['method'] ?? '';
    }

    public function error(): ?array
    {
        return $this->storage['error'] ?? null;
    }

    public function params(): ?array
    {
        return $this->storage['params'] ?? null;
    }

    public function result(): ?array
    {
        return $this->storage['result'] ?? null;
    }

    public function id(): ?int
    {
        return $this->storage['id'] ?? null;
    }

    public function firstParam(): ParamInterface
    {
        $params = $this->storage['params'] ?? [];
        return new Param(array_shift($params) ?: []);
    }

    public function firstResult(): ResultInterface
    {
        $result = $this->storage['result'] ?? [];
        return new Result(array_shift($result) ?: []);
    }

}
