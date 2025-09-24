<?php

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email
{
    public const MISSING_FROM_ERROR = 'Email is missing a valid from address';
    public const MISSING_TO_ERROR   = 'Email is missing a valid to address';
    public const NOT_SENT_ERROR     = 'Email not sent: %s';

    private MailerInterface $mailer;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['mail'] ?? null;
        if (!$config) {
            throw new \RuntimeException('No mail config found');
        }

        // Accept either a single transport or an array of transports
        $items = $config['transports']
            ?? (isset($config['transport']) ? [ $config['transport'] ] : []);

        if (!is_array($items) || !$items) {
            throw new \RuntimeException('No mail transports configured');
        }

        $built = array_map(function (array $item) {
            $scheme  = $item['scheme'];
            $host    = $item['host'];
            $user    = $item['username'];
            $pass    = $item['password'];
            $port    = $item['port'];

            $dsn = new Dsn($scheme, $host, $user, $pass, $port);

            // Build transport using Esmtp factory
            $factory = new Transport([new EsmtpTransportFactory()]);
            return $factory->fromDsnObject($dsn);
        }, $items);


        // Strategy: first (default), failover, round_robin
        $strategy = $config['strategy'] ?? 'first';
        $transport = match ($strategy) {
            'failover' => new FailoverTransport($built),
            'round_robin' => new RoundRobinTransport($built),
            default => $built[0],
        };

        $this->setMailer(new Mailer($transport));
        return $this;
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    public function setMailer(MailerInterface $mailer): self
    {
        $this->mailer = $mailer;
        return $this;
    }

    /** Build Symfony Address[] from mixed input (ignores invalid emails) */
    private function buildAddresses(string|array|null $value): array
    {
        if (empty($value)) {
            return [];
        }

        $arr = is_array($value) ? $value : [$value];
        $out = [];

        foreach ($arr as $k => $v) {
            $email = is_int($k) ? $v : $k;

            // Skip null/empty
            if (empty($email)) {
                continue;
            }

            try {
                $out[] = new Address($email);
            } catch (\InvalidArgumentException) {
                // ignore invalid addresses
            }
        }

        return $out;
    }

    /**
     * Sends an email
     * @throws EmailNotSentException
     */
    public function send(
        $fromEmail,
        $fromName,
        $to,
        $subject,
        $plainBody,
        $htmlBody,
        array $cc = [],
        array $bcc = [],
        array $docs = [],
        bool $highPriority = false
    ): void {
        $fromAddress = $this->buildAddresses([$fromEmail => $fromName]);
        if (!$fromAddress) {
            Logger::err('email failed', ['data' => self::MISSING_FROM_ERROR]);
            throw new EmailNotSentException(self::MISSING_FROM_ERROR);
        }

        $toAddresses = $this->buildAddresses($to);
        if (!$toAddresses) {
            Logger::err('email failed', ['data' => self::MISSING_TO_ERROR, 'to' => $to]);
            throw new EmailNotSentException(self::MISSING_TO_ERROR);
        }

        $email = (new SymfonyEmail())
            ->from($fromAddress[0])
            ->to(...$toAddresses)
            ->subject($subject)
            ->text($plainBody);

        if ($htmlBody !== null) {
            $email->html($htmlBody);
        }

        if ($ccList = $this->buildAddresses($cc)) {
            $email->cc(...$ccList);
        }
        if ($bccList = $this->buildAddresses($bcc)) {
            $email->bcc(...$bccList);
        }

        foreach ($docs as $doc) {
            if (!isset($doc['content'])) {
                continue;
            }
            $email->attach($doc['content'], $doc['fileName'] ?? 'attachment', 'application/octet-stream');
        }

        if ($highPriority) {
            $email->priority(SymfonyEmail::PRIORITY_HIGH);
            $email->getHeaders()->addTextHeader('Importance', 'High');
            $email->getHeaders()->addTextHeader('X-Priority', '1');
            $email->getHeaders()->addTextHeader('X-MSMail-Priority', 'High');
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $msg = sprintf(self::NOT_SENT_ERROR, $e->getMessage());
            Logger::err('email failed', ['data' => $msg]);
            throw new EmailNotSentException($msg, 0, $e);
        }
    }
}
