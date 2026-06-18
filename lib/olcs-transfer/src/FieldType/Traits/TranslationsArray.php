<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Translations Array
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait TranslationsArray
{
    /**
     * @Transfer\ArrayInput
     */
    protected $translationsArray = [];

    /**
     * @return array
     */
    public function getTranslationsArray()
    {
        return $this->translationsArray;
    }
}
