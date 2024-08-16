<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class RestrictedCountry
{
    /**
     * Create instance
     *
     * @param string $code
     * @param string $labelTranslationKey
     * @param bool checked
     *
     * @return RestrictedCountry
     */
    public function __construct(private $code, private $labelTranslationKey, private $checked)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'code' => $this->code,
            'labelTranslationKey' => $this->labelTranslationKey,
            'checked' => $this->checked,
        ];
    }
}
