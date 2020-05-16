<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class LoggedMailer implements MailerInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        try {
            $this->sendEmail($message, $envelope);
        } catch (TransportExceptionInterface $e) {
            $this->logger->critical(sprintf('Email was not sent, error message: %s', $e->getMessage()));
        }
    }

    /**
     * @param RawMessage $message
     * @param Envelope|null $envelope
     * @throws TransportExceptionInterface
     */
    private function sendEmail(RawMessage $message, Envelope $envelope = null): void
    {
        $this->logger->info(sprintf('Sending email process starts'));
        $this->mailer->send($message, $envelope);
        $this->logger->info(sprintf('Sending email process ended with no errors'));
    }
}