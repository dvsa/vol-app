<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits\Messaging;

/**
 * Trait Conversation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait MessageContent
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":3000})
     */
    protected $messageContent;

    public function getMessageContent()
    {
        return $this->messageContent;
    }
}
