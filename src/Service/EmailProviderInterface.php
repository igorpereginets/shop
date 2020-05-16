<?php

namespace App\Service;

use Symfony\Component\Mime\Message;

interface EmailProviderInterface
{
    public function getResetPasswordEmail(string $email, string $token): Message;

    public function getConfirmationEmail(string $email, string $token): Message;
}