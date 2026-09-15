<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Placeholder
 * @author Andy Newton <andy@vitri.ltd>
 */
trait Placeholder
{
    /**
     * @var string
     * @Transfer\Escape(true)
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    protected $placeholder;

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }
}
