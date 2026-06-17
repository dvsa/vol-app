<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * TranslationKey
 * @author Andy Newton <andy@vitri.ltd>
 */
trait TranslationKey
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":512})
     */
    protected $translationKey;

    /**
     * @return string
     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }
}
