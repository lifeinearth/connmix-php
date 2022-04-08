<?php

namespace Connmix\V1\Message;

use Connmix\AutoIncrement;

class ConsumeMessage
{

    /**
     * @var string
     */
    protected $format = '{"method":"queue.consume","params":["%s"],"id":%d}';

    /**
     * @var array
     */
    protected $queues = [];

    /**
     * @param array $queues
     */
    public function __construct(array $queues)
    {
        $this->queues = $queues;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return sprintf($this->format, implode('","', $this->queues), AutoIncrement::id());
    }

}
