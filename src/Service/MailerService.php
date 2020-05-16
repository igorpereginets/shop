<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var EmailProvider
     */
    private $provider;

    public function __construct(MailerInterface $mailer, EmailProviderInterface $provider)
    {
        $this->mailer = $mailer;
        $this->provider = $provider;
    }

    public function sendResetPasswordToken(string $email, string $token)
    {
        $resetEmail = $this->provider->getResetPasswordEmail($email, $token);

        $this->mailer->send($resetEmail);
    }

    public function sendConfirmationToken(string $email, string $token)
    {
        $confirmationEmail = $this->provider->getConfirmationEmail($email, $token);

        $this->mailer->send($confirmationEmail);
    }
}