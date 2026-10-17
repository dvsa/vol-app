<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait TranslationFormatOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack":{"text","editorjs"}})
     */
    protected $format;

    public function getFormat(): ?string
    {
        return $this->format;
    }
}
