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
     * @var callable
     */
    protected $onFulfilled;

    /**
     * @var callable
     */
    protected $onRejected;

    /**
     * @var int
     */
    protected $syncInterval = 60;

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
        \React\EventLoop\Loop::addTimer($this->syncInterval, $this->syncFunc());
    }

    /**
     * @param callable $onFulfilled
     * @param callable $onRejected
     * @return void
     * @throws \Exception
     */
    public function then(callable $onFulfilled, callable $onRejected): void
    {
        $this->onFulfilled = $onFulfilled;
        $this->onRejected = $onRejected;

        foreach ($this->nodes->items() as $node) {
            $host = sprintf("%s:%d", $node['ip'], $node['port']);
            $this->addEngine($host);
        }
    }

    protected function addEngine(string $host)
    {
        switch ($this->nodes->version()) {
            case 'v1':
                $engine = new EngineV1($this->onFulfilled, $this->onRejected, $this->queues, $host, $this->timeout);
                $engine->run();
                $this->engines[] = $engine;
                break;
            default:
                throw new \Exception('Invalid API version');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function syncFunc(): \Closure
    {
        return function () {
            try {
                $this->nodes->loadNodes();
            } catch (\Throwable $ex) {
                echo sprintf("ERROR: load nodes fail: %s\n", $ex->getMessage());
            }

            // 增加
            foreach ($this->nodes->items() as $node) {
                $host = sprintf("%s:%d", $node['ip'], $node['port']);
                $find = false;
                foreach ($this->engines as $engine) {
                    if ($engine->host == $host) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $this->addEngine($host);
                }
            }
            // 减少
            foreach ($this->engines as $key => $engine) {
                $find = false;
                foreach ($this->nodes->items() as $node) {
                    $host = sprintf("%s:%d", $node['ip'], $node['port']);
                    if ($engine->host == $host) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $this->engines[$key]->close();
                    unset($this->engines[$key]);
                    $this->engines = array_values($this->engines);
                }
            }

            \React\EventLoop\Loop::addTimer($this->syncInterval, $this->syncFunc());
        };
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
