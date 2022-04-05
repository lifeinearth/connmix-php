<?php

namespace Connmix\V1\Message;

use Connmix\ParamInterface;

class Param implements ParamInterface
{

    /**
     * @var array
     */
    protected $item = [];

    /**
     * @param array $item
     */
    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function clientID(): int
    {
        return $this->item['client_id'] ?? 0;
    }

    public function queue(): string
    {
        return $this->item['queue'] ?? '';
    }

    public function data(): ?array
    {
        return $this->item['data'] ?? null;
    }

}
