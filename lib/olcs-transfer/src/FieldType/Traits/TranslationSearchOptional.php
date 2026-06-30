<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Translation Key Search Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait TranslationSearchOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":1024})
     */
    protected $translationSearch;

    /**
     * @return string
     */
    public function getTranslationSearch()
    {
        return $this->translationSearch;
    }
}
