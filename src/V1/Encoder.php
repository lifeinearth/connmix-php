<?php

namespace Connmix\V1;

use Connmix\EncoderInterface;

class Encoder implements EncoderInterface
{

    /**
     * @param $data
     * @return string
     */
    public function encode($data): string
    {
        return json_encode($data);
    }

}
