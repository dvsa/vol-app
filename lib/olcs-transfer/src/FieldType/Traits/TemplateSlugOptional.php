<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Template slug
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait TemplateSlugOptional
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $templateSlug;

    /**
     * @return string
     */
    public function getTemplateSlug()
    {
        return $this->templateSlug;
    }
}
