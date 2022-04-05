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
    protected $topics = [];

    /**
     * @param array $topics
     */
    public function __construct(array $topics)
    {
        $this->topics = $topics;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return sprintf($this->format, implode('","', $this->topics), AutoIncrement::id());
    }

}
