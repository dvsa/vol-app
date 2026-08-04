<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait MessagingConversationOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $messagingConversation = null;

    public function getMessagingConversation(): ?int
    {
        return $this->messagingConversation ? (int)$this->messagingConversation : null;
    }

    /** @param int $conversationId */
    public function setMessagingConversation($conversationId)
    {
        $this->messagingConversation = $conversationId ? (int)$conversationId : null;
    }
}
