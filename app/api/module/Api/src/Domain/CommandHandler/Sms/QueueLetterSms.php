<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Sms;

use Dvsa\Olcs\Api\Domain\Command\Sms\QueueLetterSms as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Service\Sms\SendingWindow;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Sms\SmsNotQueued;
use Dvsa\Olcs\Api\Service\Sms\SmsNumber;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Write a notify_message row and a queue item for the "letter issued" SMS. DB work only.
 *
 * This runs inside the letter's transaction. If the text can't be queued, it is logged and the letter carries on:
 * see NotifyMessage::insertQueuedSms() for how the writes are kept apart from the letter's.
 */
class QueueLetterSms extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const string TEMPLATE_NAME = 'letter-issued-sms';

    protected $repoServiceName = 'Document';
    protected $extraRepos = ['DocTemplate', 'NotifyMessage'];

    private SmsGate $smsGate;
    private IdentityProviderInterface $identityProvider;

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof Cmd);

        try {
            $prepared = $this->prepare($command);
        } catch (\Throwable $e) {
            // The letter matters more than the text, so never let a read failure escape.
            $this->logFailure($command, $e);

            return $this->result;
        }

        if ($prepared === null) {
            return $this->result;
        }

        [$document, $licence, $recipient, $now] = $prepared;

        $message = new NotifyMessage(
            $this->refData(NotifyMessage::CHANNEL_SMS),
            $this->refData(NotifyMessage::STATUS_QUEUED),
            $recipient,
            $now
        );
        $message->setOrganisation($licence->getOrganisation());
        $message->setLicence($licence);
        $message->setDocument($document);
        $message->setTemplateName(self::TEMPLATE_NAME);
        $message->setDescription($document->getDescription());
        if ($document->getApplication() !== null) {
            $message->setApplication($document->getApplication());
        }
        if ($document->getBusReg() !== null) {
            $message->setBusReg($document->getBusReg());
        }
        if ($document->getCase() !== null) {
            $message->setCase($document->getCase());
        }

        try {
            $messageId = $this->getRepo('NotifyMessage')->insertQueuedSms(
                $message,
                SendSms::class,
                SendingWindow::nextAllowed($now),
                $this->createdById()
            );
        } catch (SmsNotQueued $e) {
            // nothing was written, so the letter goes ahead without its text
            $this->logFailure($command, $e->getPrevious() ?? $e);

            return $this->result;
        }

        $this->result->addId('notifyMessage', $messageId);

        return $this->result;
    }

    /**
     * All the reads that decide whether to text. Null means skip.
     *
     * @return array{Document, Licence, string, \DateTimeImmutable}|null
     */
    private function prepare(Cmd $command): ?array
    {
        if (!$this->smsGate->isEnabled()) {
            return null;
        }

        /** @var Document $document */
        $document = $this->getRepo()->fetchById($command->getDocument());

        if ($this->isSuppressedFromOperator($document)) {
            return null;
        }

        $licence = $document->getRelatedLicence();
        if ($licence === null) {
            return null;
        }

        $recipient = SmsNumber::forLicence($licence);
        if ($recipient === null) {
            return null;
        }

        // one letter text per number per SMS day, so a letter after 20:00 and one the next morning share a text
        $now = $this->now();
        [$dayStart, $dayEnd] = SendingWindow::smsDay($now);
        if ($this->getRepo('NotifyMessage')->hasSmsForRecipientBetween($recipient, self::TEMPLATE_NAME, $dayStart, $dayEnd)) {
            return null;
        }

        return [$document, $licence, $recipient, $now];
    }

    private function logFailure(Cmd $command, \Throwable $e): void
    {
        Logger::err(
            'Could not queue letter SMS',
            [
                'document_id' => $command->getDocument(),
                'exception' => [
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]
        );
    }

    /** The user the blameable listener would write, as the rows don't go through Doctrine. */
    private function createdById(): ?int
    {
        if ($this->identityProvider->getMasqueradedAsSystemUser()) {
            return IdentityProviderInterface::SYSTEM_USER;
        }

        $user = $this->getCurrentUser();

        return ($user instanceof User && !$user->isAnonymous()) ? $user->getId() : null;
    }

    protected function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    private function isSuppressedFromOperator(Document $document): bool
    {
        $metadata = json_decode((string) $document->getMetadata(), true);
        $templateId = $metadata['details']['documentTemplate'] ?? null;
        if ($templateId === null) {
            return false;
        }

        try {
            $template = $this->getRepo('DocTemplate')->fetchById($templateId);
        } catch (NotFoundException) {
            // Cannot tell whether it is for the operator, so do not text.
            return true;
        }

        return ValueHelper::isOn($template->getSuppressFromOp());
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->smsGate = $container->get(SmsGate::class);
        $this->identityProvider = $container->get(IdentityProviderInterface::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
