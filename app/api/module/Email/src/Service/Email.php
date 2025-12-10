<?php

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * Class Email
 *
 * @package Olcs\Email\Service
 * @author  Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class Email implements FactoryInterface
{
    public const MISSING_FROM_ERROR = 'Email is missing a valid from address';
    public const MISSING_TO_ERROR = 'Email is missing a valid to address';
    public const NOT_SENT_ERROR = 'Email not sent: %s';

    private MailerInterface $mailer;

    /**
     * Get Mailer.
     *
     * @return MailerInterface
     */
    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    /**
     * Set Mailer.
     *
     * @param MailerInterface $mailer mail transport
     *
     * @return $this
     */
    public function setMailer(MailerInterface $mailer): self
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * Validates the array of email addresses, excluding those which fail, and returns an array of Address objects
     *
     * The array of cc/bcc can either be in the format [email_address => name] or [0 => email_address]
     * If the key is a string, it is assumed that is the email address, and the value is the name of the recipient
     *
     * The "to" address tends to just be a string, but we're designed here to cope if an array is passed in
     *
     * @param string|array|null $addressOrAddresses email addresses
     *
     * @return array<Address>
     */
    public function validateAddresses($addressOrAddresses): array
    {
        $addressList = [];

        //null or empty string
        if (empty($addressOrAddresses)) {
            return $addressList;
        }

        //addresses we pass as string, usually a to address
        if (!is_array($addressOrAddresses)) {
            $addressOrAddresses = [$addressOrAddresses];
        }

        //addresses passed in as an array (from, cc, bcc)
        foreach ($addressOrAddresses as $key => $value) {
            $email = null;

            if (is_int($key) || is_numeric($key)) {
                $email = $value;
            } elseif (is_string($key)) {
                $email = $key;
            }

            try {
                //olcs-14825 we no longer pass in the name, as this occasionally caused problems with postfix
                $address = new Address($email);
                $addressList[] = $address;
            } catch (\Throwable) {
                //address is invalid in some way, right now these addresses are ignored
            }
        }

        return $addressList;
    }

    /**
     * Sends an email
     *
     * @param string $fromEmail From email address
     * @param string $fromName  From name
     * @param string $to        To address
     * @param string $subject   Email subject
     * @param string $plainBody Plain text email body
     * @param string $htmlBody  HTML email body
     * @param array  $cc        cc'd addresses
     * @param array  $bcc       bcc'd addresses
     * @param array  $docs      attached documents
     *
     * @return void
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
    ) {
        $fromAddress = $this->validateAddresses([$fromEmail => $fromName]);

        if (count($fromAddress) === 0) {
            Logger::err('email failed', ['data' => self::MISSING_FROM_ERROR]);
            throw new EmailNotSentException(self::MISSING_FROM_ERROR);
        }

        $toAddresses = $this->validateAddresses($to);

        if (count($toAddresses) === 0) {
            Logger::err('email failed', ['data' => self::MISSING_TO_ERROR, 'to' => $to]);
            throw new EmailNotSentException(self::MISSING_TO_ERROR);
        }

        $email = (new SymfonyEmail())
            ->from($fromAddress[0])
            ->to(...$toAddresses)
            ->subject($subject);

        // Add CC addresses
        $ccAddresses = $this->validateAddresses($cc);
        if (!empty($ccAddresses)) {
            $email->cc(...$ccAddresses);
        }

        // Add BCC addresses
        $bccAddresses = $this->validateAddresses($bcc);
        if (!empty($bccAddresses)) {
            $email->bcc(...$bccAddresses);
        }

        // Set email body
        $email->text($plainBody);
        if ($htmlBody !== null) {
            $email->html($htmlBody);
        }

        // Add attachments
        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $email->attach($doc['content'], $doc['fileName']);
            }
        }

        // Set high priority if requested
        if ($highPriority) {
            $email->priority(SymfonyEmail::PRIORITY_HIGH);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $message = sprintf(self::NOT_SENT_ERROR, $e->getMessage());
            Logger::err('email failed', ['data' => $message]);
            throw new EmailNotSentException($message, 0, $e);
        } catch (\Exception $e) {
            $message = sprintf(self::NOT_SENT_ERROR, $e->getMessage());
            Logger::err('email failed', ['data' => $message]);
            throw new EmailNotSentException($message, 0, $e);
        }
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (empty($config['mail'])) {
            throw new \RuntimeException('No mail config found');
        }

        // Build DSN from mail config
        $mailConfig = $config['mail'];
        $host = $mailConfig['options']['host'] ?? 'localhost';
        $port = $mailConfig['options']['port'] ?? 25;
        $username = $mailConfig['options']['connection_config']['username'] ?? null;
        $password = $mailConfig['options']['connection_config']['password'] ?? null;

        if ($username && $password && $username !== 'null' && $password !== 'null') {
            $dsn = sprintf('smtp://%s:%s@%s:%s', $username, $password, $host, $port);
        } else {
            $dsn = sprintf('smtp://%s:%s', $host, $port);
        }

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $this->setMailer($mailer);
        return $this;
    }
}
