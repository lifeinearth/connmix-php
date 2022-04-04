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
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function check(string $message): bool
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
        // TODO: Implement method() method.
    }

    public function clientId(): int
    {
        // TODO: Implement clientId() method.
    }

    public function queue(): string
    {
        // TODO: Implement queue() method.
    }

    public function data(): array
    {
        // TODO: Implement data() method.
    }

    public function id(): int
    {
        // TODO: Implement id() method.
    }

}
