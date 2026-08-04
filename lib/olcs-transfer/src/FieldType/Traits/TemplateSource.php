<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Template source trait
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait TemplateSource
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Escape(false)
     */
    protected $source;

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
