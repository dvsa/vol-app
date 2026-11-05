<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Local stand-in: the "SMS" lands in mailpit as a plain-text email.
 */
final class MailpitSmsTransport implements SmsTransportInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail = 'sms@localhost',
        private readonly string $fromName = '',
    ) {
    }

    #[\Override]
    public function send(string $to, string $templateId, string $body, string $reference): ?string
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to('sms@localhost')
            ->subject('SMS to ' . $to)
            ->text($body);

        $this->mailer->send($email);

        return null;
    }
}
