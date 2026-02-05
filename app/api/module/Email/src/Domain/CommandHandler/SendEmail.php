<?php

namespace Dvsa\Olcs\Email\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Email\Domain\Command\SendEmail as SendEmailCmd;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Dvsa\Olcs\Email\Service\Email as EmailService;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;

/**
 * Send Email
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SendEmail extends AbstractCommandHandler implements UploaderAwareInterface
{
    use UploaderAwareTrait;

    protected $repoServiceName = 'Document';

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $sendAllMailTo;

    /**
     * @var string
     */
    private $selfServeUri;

    /**
     * @var string
     */
    private $internalUri;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @return string
     */
    public function getSelfServeUri()
    {
        return $this->selfServeUri;
    }

    /**
     * @param string $selfServeUri
     */
    public function setSelfServeUri($selfServeUri)
    {
        $this->selfServeUri = rtrim($selfServeUri, '/');
    }

    /**
     * @return string
     */
    public function getInternalUri()
    {
        return $this->internalUri;
    }

    /**
     * @param string $internalUri
     */
    public function setInternalUri($internalUri)
    {
        $this->internalUri = rtrim($internalUri, '/');
    }

    /**
     * @return string
     */
    public function getSendAllMailTo()
    {
        return $this->sendAllMailTo;
    }

    /**
     * @param string $sendAllMailTo
     */
    public function setSendAllMailTo($sendAllMailTo)
    {
        $this->sendAllMailTo = $sendAllMailTo;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    /**
     * @return EmailService
     */
    public function getEmailService()
    {
        return $this->emailService;
    }

    public function setEmailService(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handles Send Email command
     *
     * @param CommandInterface|SendEmailCmd $command
     *
     * @return Result
     * @throws EmailNotSentException
     * @throws \RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        //make sure we have at least a plain text version
        if (empty($command->getPlainBody())) {
            throw new \RuntimeException('No message body has been set (plain text)');
        }

        $fromName = $command->getFromName();

        if ($fromName === null) {
            $fromName = $this->getFromName();
        }

        $fromEmail = $command->getFromEmail();
        if ($fromEmail === null) {
            $fromEmail = $this->getFromEmail();
        }

        $subject = $this->translate($command->getSubject(), $command->getLocale());

        $to = $command->getTo();
        $cc = $command->getCc();
        $bcc = $command->getBcc();
        $docs = $command->getDocs();

        if ($this->getSendAllMailTo()) {
            $to = $this->getSendAllMailTo();
            /**
             * IMPORTANT CC gets emptied when we have configured emails to be sent to 1 email address
             */
            $cc = [];

            /**
             * IMPORTANT BCC gets emptied when we have configured emails to be sent to 1 email address
             */
            $bcc = [];

            $originalTo = implode(', ', (array)$command->getTo());

            $subject = $originalTo . ' : ' . $subject;
        }

        $subject = vsprintf($subject, $command->getSubjectVariables());

        $plainBody = $this->replaceUris($command->getPlainBody());
        $htmlBody = $command->getHtmlBody();

        if ($htmlBody !== null) {
            $htmlBody = $this->replaceUris($htmlBody);
        }

        /**
         * @var DocumentRepo $docRepo
         * @var DocumentEntity $doc
         * @var array $fetchedDocs
         */
        $docRepo = $this->getRepo();
        $fetchedDocs = $docRepo->fetchByIds($docs);
        $downloadedDocs = [];

        foreach ($fetchedDocs as $doc) {
            $file = $this->getUploader()->download($doc->getIdentifier());
            if ($file === null) {
                throw new \RuntimeException('Unable to process attachment (empty document downloaded)');
            }
            $downloadedDocs[] = [
                'fileName' => basename((string) $doc->getFilename()),
                'content' => $file->getContent()
            ];
        }

        $this->send($to, $subject, $plainBody, $htmlBody, $fromEmail, $fromName, $cc, $bcc, $downloadedDocs, $command->isHighPriority());

        $this->result->addMessage('Email sent');
        return $this->result;
    }

    /**
     * Sends an email
     *
     * @param string $to
     * @param string $subject
     * @param string $plain
     * @param string $html
     * @param string $fromEmail
     * @param string $fromName
     *
     * @return void
     * @throws EmailNotSentException
     */
    protected function send($to, $subject, $plain, $html, $fromEmail, $fromName, array $cc, array $bcc, array $docs, bool $highPriority = false)
    {
        $this->getEmailService()->send($fromEmail, $fromName, $to, $subject, $plain, $html, $cc, $bcc, $docs, $highPriority);
    }

    /**
     * Translate a message
     *
     * @param string $message
     * @param string $locale
     *
     * @return string
     */
    protected function translate($message, $locale = 'en_GB')
    {
        return $this->getTranslator()->translate($message, TranslationLoader::DEFAULT_TEXT_DOMAIN, $locale);
    }

    /**
     * Replace selfserve / internal URI with the real URI
     *
     * @param string $text
     *
     * @return string
     */
    protected function replaceUris($text)
    {
        return strtr(
            $text,
            [
                'http://selfserve/' => $this->getSelfServeUri() . '/',
                'http://internal/' => $this->getInternalUri() . '/',
            ]
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['email']['from_name'])) {
            $this->setFromName($config['email']['from_name']);
        }
        if (isset($config['email']['from_email'])) {
            $this->setFromEmail($config['email']['from_email']);
        }
        if (isset($config['email']['send_all_mail_to'])) {
            $this->setSendAllMailTo($config['email']['send_all_mail_to']);
        }
        if (isset($config['email']['selfserve_uri'])) {
            $this->setSelfServeUri($config['email']['selfserve_uri']);
        }
        if (isset($config['email']['internal_uri'])) {
            $this->setInternalUri($config['email']['internal_uri']);
        }
        $this->setTranslator($container->get('translator'));
        $this->setEmailService($container->get('EmailService'));
        return parent::__invoke($container, $requestedName, $options);
    }
}
