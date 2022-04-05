<?php

namespace Connmix;

interface ParamInterface
{

    public function clientID(): int;

    public function queue(): string;

    public function data(): ?array;

}
