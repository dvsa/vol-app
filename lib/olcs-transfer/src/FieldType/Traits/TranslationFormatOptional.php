<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

trait TranslationFormatOptional
{
    #[Transfer\Optional]
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => ['text', 'editorjs']])]
    protected $format;

    public function getFormat(): ?string
    {
        return $this->format;
    }
}
