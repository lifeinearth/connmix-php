<?php

namespace Connmix;

use Psr\Http\Message\ResponseInterface;

class Nodes
{

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var float
     */
    protected $timeout = 10.0;


    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param string $host
     * @param float $timeout
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct(string $host, float $timeout)
    {
        $this->host = $host;
        $this->timeout = $timeout;

        $this->guzzle = new \GuzzleHttp\Client([
            'timeout' => $timeout,
        ]);

        $this->loadVersion();
        $this->loadNodes();
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadVersion(): void
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
    public function loadNodes(): void
    {
        $url = sprintf("%s/%s/nodes", $this->host, $this->version);
        $response = $this->guzzle->request('GET', $url);
        $body = static::parseBody($response);
        $this->items = $body['nodes'];
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
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return $this->version;
    }

}
