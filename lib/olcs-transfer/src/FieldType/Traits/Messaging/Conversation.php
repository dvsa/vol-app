<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits\Messaging;

/**
 * Trait Conversation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait Conversation
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $conversation;

    /**
     * @return int
     */
    public function getConversation()
    {
        return $this->conversation;
    }
}
