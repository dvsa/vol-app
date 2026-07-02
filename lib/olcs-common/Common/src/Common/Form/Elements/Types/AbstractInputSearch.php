<?php

declare(strict_types=1);

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;

abstract class AbstractInputSearch extends Fieldset
{
    public const ELEMENT_HINT_NAME = 'hint';

    public const ELEMENT_INPUT_NAME = 'search-value';

    public const ELEMENT_SUBMIT_NAME = 'submit';

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setAttribute('class', 'lookup');
        $this->addHint();
        $this->addInput();
        $this->addSubmit();
    }

    #[\Override]
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    #[\Override]
    public function getMessages(?string $elementName = null): array
    {
        return current($this->messages) ?: [];
    }

    abstract protected function addHint();

    abstract protected function addInput();

    abstract protected function addSubmit();
}
