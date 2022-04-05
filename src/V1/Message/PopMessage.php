<?php

namespace Connmix\V1\Message;

use Connmix\PopMessageInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class PopMessage implements PopMessageInterface
{

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var array
     */
    protected $storage;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
        $this->storage = json_decode($message->getContents(), true) ?: [];
    }

    /**
     * @param string $message
     * @return bool
     */
    public static function check(string $message): bool
    {
        return strpos($message, '{"method":"queue.pop","params":') === 0;
    }

    /**
     * @return MessageInterface
     */
    public function rawMessage(): MessageInterface
    {
        return $this->message;
    }

    public function method(): string
    {
        return $this->storage['method'] ?? '';
    }

    public function clientId(): int
    {
        return $this->storage['params']['client_id'] ?? 0;
    }

    public function queue(): string
    {
        return $this->storage['params']['queue'] ?? '';
    }

    public function data(): array
    {
        return $this->storage['params']['data'] ?? [];
    }

    public function id(): ?int
    {
        return $this->storage['id'] ?? null;
    }

}
