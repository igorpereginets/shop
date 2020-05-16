<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class EmailProvider implements EmailProviderInterface
{
    /**
     * @var string
     */
    private $noreplyEmail;
    /**
     * @var string
     */
    private $adminEmail;

    public function __construct(string $noreplyEmail, string $adminEmail)
    {
        $this->noreplyEmail = $noreplyEmail;
        $this->adminEmail = $adminEmail;
    }

    public function getResetPasswordEmail(string $email, string $token): Message
    {
        $email = (new Email())
            ->from($this->noreplyEmail)
            ->to($email)
            ->subject('Reset Password')
            ->returnPath($this->adminEmail)
            ->text('Token: ' . $token)
            ->html('<h1>Token:</h1><span>' . $token . '</span>');

        $email->ensureValidity();

        return $email;
    }

    public function getConfirmationEmail(string $email, string $token): Message
    {
        $email = (new Email())
            ->from($this->noreplyEmail)
            ->to($email)
            ->subject('Confirm your account')
            ->returnPath($this->adminEmail)
            ->text('Confirmation token: ' . $token)
            ->html('<h1>Confirmation token:</h1><span>' . $token . '</span>');

        $email->ensureValidity();

        return $email;
    }
}