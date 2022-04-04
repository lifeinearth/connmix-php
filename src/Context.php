<?php

namespace Connmix;

use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Context
{

    /**
     * @var WebSocket
     */
    protected $conn;

    /**
     * @var PopMessageInterface
     */
    protected $message;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @param WebSocket $conn
     * @param PopMessageInterface $message
     * @param EncoderInterface $encoder
     */
    public function __construct(WebSocket $conn, PopMessageInterface $message, EncoderInterface $encoder)
    {
        $this->conn = $conn;
        $this->message = $message;
        $this->encoder = $encoder;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->message->method();
    }

    /**
     * @return int
     */
    public function clientId(): int
    {
        return $this->message->clientId();
    }

    /**
     * @return string
     */
    public function queue(): string
    {
        return $this->message->queue();
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->message->data();
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->message->id();
    }

    /**
     * @return MessageInterface
     */
    public function message(): MessageInterface
    {
        return $this->message->rawMessage();
    }

    public function send(string $method, array $params = []): array
    {
        $message = $this->encoder->encode([
            'method' => $method,
            'params' => $params,
            'id' => AutoIncrement::id(),
        ]);
        $this->conn->send($message);
    }

    public function connCall(int $clientId, string $method, array $params): array
    {
        $this->send('conn.call', [
            'client_id' => $clientId,
            'method' => $method,
            'params' => $params,
        ]);
    }

    public function setContextValue(int $clientId, string $key, $value): array
    {
        return $this->connCall($clientId, 'set_context_value', [
            $key => $value,
        ]);
    }

    public function subscribe(int $clientId, string ...$channels): array
    {
        return $this->connCall($clientId, 'subscribe', $channels);
    }

    public function meshSend(int $clientId, string $data): array
    {
        $this->send('mesh.send', [
            'client_id' => $clientId,
            'data' => $data,
        ]);
    }

    public function meshPublish(string $channel, string $data): array
    {
        $this->send('mesh.send', [
            'channel' => $channel,
            'data' => $data,
        ]);
    }

}
