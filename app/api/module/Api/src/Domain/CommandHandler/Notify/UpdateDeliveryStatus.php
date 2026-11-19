<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Notify;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Records the delivery status GOV.UK Notify posts back to us. Nothing found or recognised is logged and
 * ignored rather than failed, as Notify keeps retrying a callback that gets an error.
 */
class UpdateDeliveryStatus extends AbstractCommandHandler
{
    private const array STATUSES = [
        'sent' => NotifyMessage::STATUS_SENT,
        'delivered' => NotifyMessage::STATUS_DELIVERED,
        'permanent-failure' => NotifyMessage::STATUS_PERMANENT_FAILURE,
        'temporary-failure' => NotifyMessage::STATUS_TEMPORARY_FAILURE,
        'technical-failure' => NotifyMessage::STATUS_TECHNICAL_FAILURE,
    ];

    protected $repoServiceName = 'NotifyMessage';

    private SmsConfig $config;

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $notifyId = $command->getId();
        $status = self::STATUSES[$command->getStatus()] ?? null;

        if ($status === null) {
            Logger::info('Notify callback ignored, unknown status', ['notifyId' => $notifyId, 'status' => $command->getStatus()]);

            return $this->result;
        }

        $message = $this->getRepo()->fetchByNotifyId($notifyId) ?? $this->fetchByReference($command->getReference());

        if ($message === null) {
            Logger::info('Notify callback ignored, no message with that id', ['notifyId' => $notifyId]);

            return $this->result;
        }

        // found by reference, so the send never recorded itself: treat it as sent, as SendSms does
        if ($message->getNotifyId() === null) {
            $message->setNotifyId($notifyId);
            $message->setErrorCode(null);
            $message->setErrorMessage(null);

            if ($message->getSentOn() === null) {
                $message->setSentOn(new \DateTime($command->getSentAt() ?? 'now'));
            }
        }

        $message->setStatus($this->refData($status));
        $message->setStatusUpdatedOn(new \DateTime());

        if ($command->getCompletedAt() !== null) {
            $message->setCompletedOn(new \DateTime($command->getCompletedAt()));
        }

        $this->getRepo()->save($message);
        $this->result->addMessage('Notify delivery status recorded');

        return $this->result;
    }

    /**
     * A send that timed out may not have saved Notify's id yet, so fall back on the reference we sent with it. Only
     * this environment's own reference counts (environments can share a Notify service), and only for a row that
     * still has no id.
     */
    private function fetchByReference(?string $reference): ?NotifyMessage
    {
        if ($reference === null || !preg_match('/-(\d+)$/', $reference, $matches)) {
            return null;
        }

        $rowId = (int) $matches[1];

        if ($this->config->smsReference($rowId) !== $reference) {
            return null;
        }

        try {
            $message = $this->getRepo()->fetchById($rowId);
        } catch (NotFoundException) {
            return null;
        }

        // no template id means it never went to Notify (the same check SendSms uses), so there can't be a delivery
        if ($message->getNotifyId() !== null || $message->getNotifyTemplateId() === null) {
            return null;
        }

        return $message;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->config = $container->get(SmsConfig::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
