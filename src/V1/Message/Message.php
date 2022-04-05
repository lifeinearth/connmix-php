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

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->storage['method'] ?? '';
    }

    /**
     * @return array|null
     */
    public function error(): ?array
    {
        return $this->storage['error'] ?? null;
    }

    /**
     * @return array|null
     */
    public function params(): ?array
    {
        return $this->storage['params'] ?? null;
    }

    /**
     * @return array|null
     */
    public function result(): ?array
    {
        return $this->storage['result'] ?? null;
    }

    /**
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->storage['id'] ?? null;
    }

    /**
     * @return ParamInterface
     */
    public function firstParam(): ParamInterface
    {
        if (!isset($this->storage['params'][0])) {
            return new Param();
        }
        return new Param($this->storage['params'][0]);
    }

    /**
     * @return ResultInterface
     */
    public function firstResult(): ResultInterface
    {
        if (!isset($this->storage['result'][0])) {
            return new Result();
        }
        return new Result($this->storage['result'][0]);
    }

    /**
     * @return int
     */
    public function clientID(): int
    {
        return $this->firstParam()->clientID();
    }

    /**
     * @return string
     */
    public function queue(): string
    {
        return $this->firstParam()->queue();
    }

    /**
     * @return array|null
     */
    public function data(): ?array
    {
        return $this->firstParam()->data();
    }

    /**
     * @return bool
     */
    public function success(): bool
    {
        return $this->firstResult()->success();
    }

    /**
     * @return int
     */
    public function fail(): int
    {
        return $this->firstResult()->fail();
    }

    /**
     * @return int
     */
    public function total(): int
    {
        return $this->firstResult()->total();
    }

}
