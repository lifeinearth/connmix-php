<?php

namespace Connmix;

use Connmix\V1\Engine as EngineV1;
use Psr\Http\Message\ResponseInterface;

class Client
{

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var array
     */
    protected $nodes = [];

    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * @var float
     */
    protected $timeout = 10.0;

    /**
     * @var Consumer[]
     */
    protected $consumers = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->guzzle = new \GuzzleHttp\Client([
            'timeout' => $this->timeout,
        ]);

        $this->loadVersion();
        $this->loadNodes();
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function loadVersion(): void
    {
        $url = sprintf("%s/version", $this->host);
        $response = $this->guzzle->request('GET', $url);
        $body = static::parseBody($response);
        $api = $body['api'];
        $this->version = array_shift($api);
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function loadNodes(): void
    {
        $url = sprintf("%s/%s/nodes", $this->host, $this->version);
        $response = $this->guzzle->request('GET', $url);
        $body = static::parseBody($response);
        $this->nodes = $body['nodes'];
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    protected static function parseBody(ResponseInterface $response): array
    {
        $body = $response->getBody()->__toString();
        return json_decode($body, true);
    }

    /**
     * @param string ...$queues
     * @return Consumer
     */
    public function consume(string ...$queues): Consumer
    {
        $consumer = new Consumer($this->nodes, $this->version, $this->timeout, $queues);
        $this->consumers[] = $consumer;
        return $consumer;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        foreach ($this->consumers as $consumer) {
            $consumer->close();
        }

        $loop = \React\EventLoop\Loop::get();
        $loop->stop();
    }

}
