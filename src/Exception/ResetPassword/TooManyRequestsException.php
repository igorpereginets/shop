<?php

namespace App\Exception\ResetPassword;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TooManyRequestsException extends HttpException
{
    public function __construct
    (
        int $statusCode = 400,
        string $message = 'You have already requested a reset password email. Please check your email or try again soon.',
        \Throwable $previous = null,
        array $headers = [],
        ?int $code = 0
    )
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}