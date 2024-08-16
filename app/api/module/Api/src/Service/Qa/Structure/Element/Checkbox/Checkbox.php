<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class Checkbox implements ElementInterface
{
    public const LABEL_KEY = 'label';

    /**
     * Create instance
     *
     * @param bool $checked
     *
     * @return Checkbox
     */
    public function __construct(private readonly TranslateableText $label, private readonly TranslateableText $notCheckedMessage, private $checked)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            self::LABEL_KEY => $this->label->getRepresentation(),
            'notCheckedMessage' => $this->notCheckedMessage->getRepresentation(),
            'checked' => ($this->checked ? true : false)
        ];
    }
}
