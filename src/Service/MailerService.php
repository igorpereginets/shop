<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationToken(string $email, string $token)
    {
        $email = (new Email())
            ->from('noreply@shop.com')
            ->to($email)
            ->subject('Reset Password')
            ->text('Token: ' . $token)
            ->html('<h1>Token:</h1><span>' . $token . '</span>');

        $this->mailer->send($email);
    }
}