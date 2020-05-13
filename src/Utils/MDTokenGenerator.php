<?php

namespace App\Utils;

class MDTokenGenerator
{
    public function generate(): string
    {
        return md5(random_bytes(20));
    }
}