<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Markup trait
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 *
 */
trait Markup
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Escape(false)
     */
    protected $markup;

    /**
     * @return string
     */
    public function getMarkup()
    {
        return $this->markup;
    }
}
