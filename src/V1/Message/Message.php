<?php

namespace Connmix\V1\Message;

use Connmix\MessageInterface;
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
        if ($this->event() !== '') {
            return 'consume';
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
     * @return string
     */
    public function event(): string
    {
        return $this->storage['event'] ?? '';
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
     * @return int
     */
    public function clientID(): int
    {
        $result = $this->result();
        if (!$result) {
            return 0;
        }
        return $result['client_id'] ?? 0;
    }

    /**
     * @return string
     */
    public function queue(): string
    {
        $result = $this->result();
        if (!$result) {
            return '';
        }
        return $result['queue'] ?? '';
    }

    /**
     * @return array|null
     */
    public function data(): ?array
    {
        $result = $this->result();
        if (!$result) {
            return [];
        }
        return $result['data'] ?? [];
    }

    /**
     * @return bool
     */
    public function success(): bool
    {
        $result = $this->result();
        if (!$result) {
            return false;
        }
        return $result['success'] ?? false;
    }

    /**
     * @return int
     */
    public function fail(): int
    {
        $result = $this->result();
        if (!$result) {
            return false;
        }
        return $result['fail'] ?? 0;
    }

    /**
     * @return int
     */
    public function total(): int
    {
        $result = $this->result();
        if (!$result) {
            return false;
        }
        return $result['total'] ?? 0;
    }

}
