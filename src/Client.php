<?php

namespace Connmix;

use Connmix\V1\Node as NodeV1;

class Client
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
     * @var Nodes
     */
    protected $nodes;

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

        $this->nodes = new Nodes($this->host, $this->timeout);
    }


    /**
     * @param string ...$queues
     * @return Consumer
     */
    public function consume(string ...$queues): Consumer
    {
        $consumer = new Consumer($this->nodes, $this->timeout, $queues);
        $this->consumers[] = $consumer;
        return $consumer;
    }

    /**
     * @return NodeInterface
     * @throws \Exception
     */
    public function random(): NodeInterface
    {
        $nodes = $this->nodes->items();
        $node = $nodes[array_rand($nodes)];
        switch ($this->nodes->version()) {
            case 'v1':
                $url = sprintf("ws://%s:%d/ws/v1", $node['ip'], $node['port']);
                return new NodeV1($url);
            default:
                throw new \Exception('Invalid API version');
        }
    }

    /**
     * @param string $ip
     * @param int $port
     * @param string $version
     * @return NodeInterface
     * @throws \Exception
     */
    public function node(string $ip, int $port, string $version): NodeInterface
    {
        switch ($version) {
            case 'v1':
                $url = sprintf("ws://%s:%d/ws/v1", $ip, $port);
                return new NodeV1($url);
            default:
                throw new \Exception('Invalid API version');
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        foreach ($this->consumers as $consumer) {
            $consumer->close();
        }

        $this->nodes->close();

        $loop = \React\EventLoop\Loop::get();
        $loop->stop();
    }

}
