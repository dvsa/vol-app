<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Sms;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Sms\QueueLetterSms as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\Sms\SendingWindow;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Sms\SmsNumber;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Psr\Container\ContainerInterface;

/**
 * Write a notify_message row and a queue item for the "letter issued" SMS. DB work only.
 */
class QueueLetterSms extends AbstractCommandHandler
{
    use QueueAwareTrait;

    public const string TEMPLATE_NAME = 'letter-issued-sms';

    protected $repoServiceName = 'Document';
    protected $extraRepos = ['DocTemplate', 'NotifyMessage'];

    private SmsGate $smsGate;

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof Cmd);

        if (!$this->smsGate->isEnabled()) {
            return $this->result;
        }

        /** @var Document $document */
        $document = $this->getRepo()->fetchById($command->getDocument());

        if ($this->isSuppressedFromOperator($document)) {
            return $this->result;
        }

        $licence = $document->getRelatedLicence();
        if ($licence === null) {
            return $this->result;
        }

        $recipient = SmsNumber::forLicence($licence);
        if ($recipient === null) {
            return $this->result;
        }

        $now = $this->now();
        $londonDay = $now->setTimezone(new \DateTimeZone('Europe/London'));
        if ($this->getRepo('NotifyMessage')->hasSmsForRecipientOn($recipient, $londonDay)) {
            return $this->result;
        }

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

        $this->getRepo('NotifyMessage')->save($message);
        $messageId = $message->getId();

        $processAfter = SendingWindow::nextAllowed($now);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createQueue(
                    $messageId,
                    Queue::TYPE_SMS,
                    ['commandClass' => SendSms::class, 'commandData' => ['id' => $messageId]],
                    $processAfter?->format('Y-m-d H:i:s')
                )
            )
        );
        $this->result->addId('notifyMessage', $messageId);

        return $this->result;
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

        $template = $this->getRepo('DocTemplate')->fetchById($templateId);

        return $template !== null && ValueHelper::isOn($template->getSuppressFromOp());
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->smsGate = $container->get(SmsGate::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
