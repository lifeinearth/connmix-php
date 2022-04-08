<?php

namespace Connmix\V1;

use Connmix\AutoIncrement;
use Connmix\V1\Message\Message;

class Node
{

    /**
     * @var \WebSocket\Client
     */
    protected $client;

    /**
     * @var Encoder
     */
    protected $encoder;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->client = new \WebSocket\Client($url);
        $this->encoder = new Encoder();
    }

    /**
     * @param string $method
     * @param array $params
     * @return Message
     */
    public function send(string $method, array $params = []): Message
    {
        $id = AutoIncrement::id();
        $message = $this->encoder->encode([
            'method' => $method,
            'params' => $params,
            'id' => AutoIncrement::id(),
        ]);
        $this->client->send($message);
        return new Message($this->client->receive());
    }

    /**
     * @param int $clientId
     * @param string $data
     * @return Message
     */
    public function meshSend(int $clientId, string $data): Message
    {
        return $this->send('mesh.send', [
            'client_id' => $clientId,
            'data' => $data,
        ]);
    }

    /**
     * @param string $channel
     * @param string $data
     * @return Message
     */
    public function meshPublish(string $channel, string $data): Message
    {
        return $this->send('mesh.send', [
            'channel' => $channel,
            'data' => $data,
        ]);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->client->close(1000, '');
    }

}
