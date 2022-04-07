<?php

namespace Connmix;

use Connmix\V1\Encoder;
use Connmix\V1\Engine as EngineV1;

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
     * @var EngineV1
     */
    protected $engine;

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

    protected function getConn(): \Ratchet\Client\WebSocket
    {
        // 寻找可用的ws连接
        $connList = [];
        foreach ($this->consumers as $consumer) {
            foreach ($consumer->engines as $engine) {
                if (!empty($engine->conn)) {
                    $connList[] = $engine->conn;
                }
            }
        }
        // 没有找到就创建一个
        if (empty($connList)) {
            if (!isset($this->engine)) {
                $nodes = $this->nodes->items();
                $node = array_rand($nodes);
                $host = sprintf("%s:%d", $node['ip'], $node['port']);
                $this->engine = Consumer::newEngine($this->nodes->version(), $host, $this->timeout);
            } else {
                // 检查engine是否还在最新的nodes中
                $find = false;
                foreach ($this->nodes as $node) {
                    $host = sprintf("%s:%d", $node['ip'], $node['port']);
                    if ($this->engine->host == $host) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $this->engine->close();
                    $this->engine = null;
                    return $this->getConn();
                }
            }
            $connList = [$this->engine->conn];
        }
        return $connList[array_rand($connList)];
    }

    /**
     * @param int $clientId
     * @param string $data
     * @return int
     */
    public function meshSend(int $clientId, string $data): int
    {
        $conn = $this->getConn();
        $ctx = new Context($conn, null, new Encoder());
        return $ctx->meshSend($clientId, $data);
    }

    /**
     * @param string $channel
     * @param string $data
     * @return int
     */
    public function meshPublish(string $channel, string $data): int
    {
        $conn = $this->getConn();
        $ctx = new Context($conn, null, new Encoder());
        return $ctx->meshPublish($channel, $data);
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
