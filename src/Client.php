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
     * @var EngineV1[]
     */
    protected $engines = [];

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
        $url = sprintf("%s/$%s/nodes", $this->host, $this->version);
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
     * @param callable $onFulfilled
     * @param callable $onRejected
     * @param string ...$topics
     * @return void
     * @throws \Exception
     */
    public function consume(callable $onFulfilled, callable $onRejected, string ...$topics): void
    {
        if (!empty($this->engines)) {
            throw new \Exception('Unable to repeat consuming while already consuming');
        }

        foreach ($this->nodes as $node) {
            $host = sprintf("%s:%d", $node['ip'], $node['port']);
            switch ($this->version) {
                case '/v1':
                    $this->engines[] = new EngineV1($onFulfilled, $onRejected, $topics, $host, $this->timeout);
                    break;
                default:
                    throw new \Exception('Invalid API version');
            }
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        foreach ($this->engines as $engine) {
            $engine->close();
        }

        $loop = \React\EventLoop\Loop::get();
        $loop->stop();
    }

}
