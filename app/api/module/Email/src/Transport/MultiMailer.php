<?php

namespace Dvsa\Olcs\Email\Transport;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Envelope;

final class MultiMailer implements MailerInterface
{
    /** @param MailerInterface[] $mailers */
    public function __construct(private array $mailers) {}

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $error = null;
        foreach ($this->mailers as $m) {
            try { $m->send($message, $envelope); }
            catch (TransportExceptionInterface $e) { $error ??= $e; } // try all, report first error
        }
        if ($error) { throw $error; }
    }
}
