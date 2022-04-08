<?php

namespace Connmix;

use Ratchet\Client\WebSocket;

class Context
{

    /**
     * @var WebSocket
     */
    protected $conn;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @param WebSocket $conn
     * @param MessageInterface $message
     */
    public function __construct(WebSocket $conn, MessageInterface $message, EncoderInterface $encoder)
    {
        $this->conn = $conn;
        $this->message = $message;
        $this->encoder = $encoder;
    }

    /**
     * @return MessageInterface
     */
    public function message(): MessageInterface
    {
        return $this->message;
    }

    /**
     * @param string $method
     * @param array $params
     * @return int
     */
    public function send(string $method, array $params = []): int
    {
        $id = AutoIncrement::id();
        $message = $this->encoder->encode([
            'method' => $method,
            'params' => $params,
            'id' => $id,
        ]);
        $this->conn->send($message);
        return $id;
    }

    /**
     * @param int $clientId
     * @param string $method
     * @param array $params
     * @return int
     */
    public function connCall(int $clientId, string $method, array $params): int
    {
        return $this->send('conn.call', [
            'client_id' => $clientId,
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * @param int $clientId
     * @param string $key
     * @param $value
     * @return int
     */
    public function setContextValue(int $clientId, string $key, $value): int
    {
        return $this->connCall($clientId, 'set_context_value', [
            $key => $value,
        ]);
    }

    /**
     * @param int $clientId
     * @param string ...$channels
     * @return int
     */
    public function subscribe(int $clientId, string ...$channels): int
    {
        return $this->connCall($clientId, 'subscribe', $channels);
    }

    /**
     * @param int $clientId
     * @param string $data
     * @return int
     */
    public function meshSend(int $clientId, string $data): int
    {
        return $this->send('mesh.send', [
            'client_id' => $clientId,
            'data' => $data,
        ]);
    }

    /**
     * @param string $channel
     * @param string $data
     * @return int
     */
    public function meshPublish(string $channel, string $data): int
    {
        return $this->send('mesh.send', [
            'channel' => $channel,
            'data' => $data,
        ]);
    }

}
