<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Notify;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * NotifyMessage Entity
 */
#[ORM\Table(name: 'notify_message')]
#[ORM\Entity]
class NotifyMessage extends AbstractNotifyMessage
{
    public const CHANNEL_EMAIL = 'nmc_email';
    public const CHANNEL_SMS = 'nmc_sms';
    public const CHANNEL_LETTER = 'nmc_letter';

    public const STATUS_QUEUED = 'nms_queued';
    public const STATUS_CANCELLED = 'nms_cancelled';
    public const STATUS_REJECTED = 'nms_rejected';
    public const STATUS_FAILED = 'nms_failed';
    public const STATUS_CREATED = 'nms_created';
    public const STATUS_SENDING = 'nms_sending';
    public const STATUS_PENDING = 'nms_pending';
    public const STATUS_SENT = 'nms_sent';
    public const STATUS_DELIVERED = 'nms_delivered';
    public const STATUS_PERMANENT_FAILURE = 'nms_permanent_failure';
    public const STATUS_TEMPORARY_FAILURE = 'nms_temporary_failure';
    public const STATUS_TECHNICAL_FAILURE = 'nms_technical_failure';

    public function __construct(RefData $channel, RefData $status, string $recipient, \DateTimeImmutable $requestedOn)
    {
        parent::__construct();
        $this->channel = $channel;
        $this->status = $status;
        $this->recipient = $recipient;
        $this->requestedOn = $requestedOn;
    }
}
