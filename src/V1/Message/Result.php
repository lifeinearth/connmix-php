<?php

namespace Connmix\V1\Message;

use Connmix\ResultInterface;

class Result implements ResultInterface
{

    /**
     * @var array
     */
    protected $iterm = [];

    /**
     * @param array $item
     */
    public function __construct(array $item = [])
    {
        $this->iterm = $item;
    }

    /**
     * @return array|null
     */
    public function error(): ?array
    {
        return $this->iterm['error'] ?? null;
    }

    public function success(): bool
    {
        return $this->iterm['success'] ?? false;
    }

    public function fail(): int
    {
        return $this->iterm['fail'] ?? 0;
    }

    public function total(): int
    {
        return $this->iterm['total'] ?? 0;
    }

}
