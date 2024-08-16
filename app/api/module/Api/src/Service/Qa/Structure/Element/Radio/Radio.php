<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class Radio implements ElementInterface
{
    /**
     * Create instance
     *
     *
     * @return Radio
     */
    public function __construct(private readonly OptionList $optionList, private readonly TranslateableText $notSelectedMessage, private readonly mixed $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'options' => $this->optionList->getRepresentation(),
            'notSelectedMessage' => $this->notSelectedMessage->getRepresentation(),
            'value' => $this->value
        ];
    }
}
