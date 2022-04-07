<?php

namespace Connmix\V1;

use Connmix\Context;
use Connmix\V1\Message\ConsumeMessage;
use Connmix\V1\Message\Message;

class Engine
{

    /**
     * @var \Closure
     */
    protected $onFulfilled;

    /**
     * @var \Closure
     */
    protected $onRejected;

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var float
     */
    protected $timeout = 0.0;

    /**
     * @var ConsumeMessage
     */
    protected $message;

    /**
     * @var \Ratchet\Client\WebSocket
     */
    protected $conn;

    /**
     * @param callable $onFulfilled
     * @param callable $onRejected
     * @param array $topics
     * @param string $host
     * @param float $timeout
     */
    public function __construct(callable $onFulfilled, callable $onRejected, array $topics, string $host, float $timeout)
    {
        $this->onFulfilled = $onFulfilled;
        $this->onRejected = $onRejected;
        $this->host = $host;
        $this->timeout = $timeout;
        $this->message = new ConsumeMessage($topics);
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $loop = \React\EventLoop\Loop::get();
        $reactConnector = new \React\Socket\Connector([
            'timeout' => $this->timeout,
        ]);
        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);
        $url = sprintf('ws://%s/ws/v1', $this->host);
        $connector($url, [], [])
            ->then(function (\Ratchet\Client\WebSocket $conn) use ($url) {
                $this->conn = $conn;
                $onFulfilled = $this->onFulfilled;
                $onRejected = $this->onRejected;

                $conn->on('message', function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn, $onFulfilled, $onRejected) {
                    try {
                        $receiveMessage = new Message($msg);
                        $encoder = new Encoder();
                        $onFulfilled(new Context($conn, $receiveMessage, $encoder));
                    } catch (\Throwable $e) {
                        $onRejected($e);
                    }
                });

                $conn->on('close', function ($code = null, $reason = null) use ($onRejected, $url) {
                    $onRejected(new \Exception(sprintf('Client connection closed (code=%d, reason=%s, url=%s)', $code, $reason, $url)));
                    \React\EventLoop\Loop::addTimer(1, [$this, 'run']);
                });

                try {
                    $conn->send($this->message->getContents());
                } catch (\Throwable $e) {
                    $onRejected($e);
                }
            }, function (\Throwable $e) use ($loop) {
                $onRejected = $this->onRejected;
                $onRejected($e);
                \React\EventLoop\Loop::addTimer(1, [$this, 'run']);
            });
    }

    public function close()
    {
        $this->conn and $this->conn->close();
    }

}
