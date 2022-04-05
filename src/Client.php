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
