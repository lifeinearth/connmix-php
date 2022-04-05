<?php

namespace Connmix;

interface ResultInterface
{

    public function success(): bool;

    public function fail(): int;

    public function total(): int;

}
