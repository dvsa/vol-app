<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * A dedicated Symfony Mailer instance, separate from the global Email service mailer, used
 * exclusively by the admin "Send test via Notify" action (VOL-7238).
 *
 * Wired from `config['email']['notify_test']['dsn']`. When that DSN is empty or unset (e.g.
 * production after cutover, or any env that doesn't want the admin test button), the service
 * is "disabled": `isEnabled()` returns false and `send()` throws. The admin UI consults
 * `isEnabled()` to decide whether to render the button.
 *
 * This lets dev/int admins exercise the Notify path against a Notify test-mode key BEFORE
 * the env-level `mail.dsn` is flipped to govuknotify://, without disturbing the production
 * pipeline currently going via SMTP.
 */
class NotifyTestMailer
{
    private ?MailerInterface $mailer;

    public function __construct(?MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Static factory used by the Laminas service factory. Returns a usable instance when the
     * DSN is populated; a disabled instance otherwise (so `isEnabled()` is false).
     */
    public static function fromDsn(?string $dsn, GovUkNotifyTransportFactory $govUkFactory): self
    {
        if (!is_string($dsn) || $dsn === '' || preg_match('/^%[^%]+%$/', $dsn)) {
            return new self(null);
        }

        $factories = iterator_to_array(Transport::getDefaultFactories());
        $factories[] = $govUkFactory;

        $transport = (new Transport($factories))->fromDsnObject(Dsn::fromString($dsn));
        return new self(new Mailer($transport));
    }

    public function isEnabled(): bool
    {
        return $this->mailer !== null;
    }

    /**
     * @throws \RuntimeException when the mailer is not configured
     * @throws TransportExceptionInterface on send failure
     */
    public function send(SymfonyEmail $email): void
    {
        if ($this->mailer === null) {
            throw new \RuntimeException(
                'NotifyTestMailer is not configured. Set config[email][notify_test][dsn] to enable test sends.'
            );
        }

        $this->mailer->send($email);
    }
}
