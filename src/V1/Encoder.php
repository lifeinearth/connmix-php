<?php

namespace Connmix\V1;

use Connmix\EncoderInterface;

class Encoder implements EncoderInterface
{

    public function encode($data): string
    {
        return json_encode($data);
    }

}
