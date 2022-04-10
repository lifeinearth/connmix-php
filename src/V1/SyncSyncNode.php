<?php

namespace Connmix\V1;

use Connmix\AutoIncrement;
use Connmix\MessageInterface;
use Connmix\SyncNodeInterface;
use Connmix\V1\Message\Message;

class SyncSyncNode implements SyncNodeInterface
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
     * @return MessageInterface
     */
    public function send(string $method, array $params = []): MessageInterface
    {
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
     * @return MessageInterface
     */
    public function meshSend(int $clientId, string $data): MessageInterface
    {
        return $this->send('mesh.send', [
            'client_id' => $clientId,
            'data' => $data,
        ]);
    }

    /**
     * @param string $channel
     * @param string $data
     * @return MessageInterface
     */
    public function meshPublish(string $channel, string $data): MessageInterface
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

    public function message(): MessageInterface
    {
        throw new \Exception(sprintf("The '%s' method is not supported in synchronization mode", __METHOD__));
    }

    public function connCall(int $clientId, string $method, array $params): int
    {
        throw new \Exception(sprintf("The '%s' method is not supported in synchronization mode", __METHOD__));
    }

    public function setContextValue(int $clientId, string $key, $value): int
    {
        throw new \Exception(sprintf("The '%s' method is not supported in synchronization mode", __METHOD__));
    }

    public function subscribe(int $clientId, string ...$channels): int
    {
        throw new \Exception(sprintf("The '%s' method is not supported in synchronization mode", __METHOD__));
    }

}
