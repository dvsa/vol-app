<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait MessagingMessageOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $messagingMessage = null;

    public function getMessagingMessage(): ?int
    {
        return $this->messagingMessage ? (int)$this->messagingMessage : null;
    }

    /** @param int $messageId */
    public function setMessagingMessage($messageId)
    {
        $this->messagingMessage = $messageId ? (int)$messageId : null;
    }
}
