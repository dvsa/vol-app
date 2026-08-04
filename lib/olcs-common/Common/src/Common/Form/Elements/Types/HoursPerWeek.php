<?php

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;

class HoursPerWeek extends Fieldset
{
    #[\Override]
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    #[\Override]
    public function getMessages(?string $elementName = null): array
    {
        return $this->messages;
    }
}
