<?php

namespace Connmix;

use Connmix\V1\Engine as EngineV1;

class Consumer
{

    /**
     * @var Nodes
     */
    protected $nodes;

    /**
     * @var float
     */
    protected $timeout = 0.0;

    /**
     * @var array
     */
    protected $queues = [];

    /**
     * @var EngineV1[]
     */
    protected $engines = [];

    /**
     * @param Nodes $nodes
     * @param float $timeout
     * @param array $queues
     */
    public function __construct(Nodes $nodes, float $timeout, array $queues)
    {
        $this->nodes = $nodes;
        $this->timeout = $timeout;
        $this->queues = $queues;
    }

    /**
     * @param callable $onFulfilled
     * @param callable $onRejected
     * @return void
     * @throws \Exception
     */
    public function then(callable $onFulfilled, callable $onRejected): void
    {
        foreach ($this->nodes->items() as $node) {
            $host = sprintf("%s:%d", $node['ip'], $node['port']);
            switch ($this->nodes->version()) {
                case 'v1':
                    $engine = new EngineV1($onFulfilled, $onRejected, $this->queues, $host, $this->timeout);
                    $engine->run();
                    $this->engines[] = $engine;
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
    }

}
