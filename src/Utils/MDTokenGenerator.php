<?php

namespace App\Utils;

class MDTokenGenerator
{
    private const MIN_LENGTH = 10;
    private const MAX_LENGTH = 32;

    public function generate(int $length = 32): string
    {
        $this->ensureLengthIsValid($length);

        $token = md5(random_bytes($length));
        $tokenLength = strlen($token);

        if ($tokenLength > $length) {
            $token = substr($token, 0, $length);
        }

        return $token;
    }

    private function ensureLengthIsValid(int $length)
    {
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('Length could be in range from %d and %d', self::MIN_LENGTH, self::MAX_LENGTH));
        }
    }
}