<?php

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Dvsa\Olcs\Email\Transport\ArchivingMailer;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email /* implements FactoryInterface (keep if needed) */
{
    public const MISSING_FROM_ERROR = 'Email is missing a valid from address';
    public const MISSING_TO_ERROR   = 'Email is missing a valid to address';
    public const NOT_SENT_ERROR     = 'Email not sent: %s';

    private MailerInterface $mailer;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): static
    {
        $config = $container->get('config');

        if (empty($config['mail'])) {
            throw new \RuntimeException('No mail config found');
        }

        // Build DSN from existing PHP config (no env change)
        $dsn    = MailDsnBuilder::buildFromConfig($config['mail']);
        $mailer = new Mailer(Transport::fromDsn($dsn));

        // Optional S3 archiving decorator
        if (!empty($config['mail']['options']['archive_to_s3']['bucket'])) {
            $s3     = $container->get(\Aws\S3\S3Client::class);
            $bucket = $config['mail']['options']['archive_to_s3']['bucket'];
            $mailer = new ArchivingMailer($mailer, $s3, $bucket);
        }

        $this->setMailer($mailer);
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
