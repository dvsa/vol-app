<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait TemplateFolder
 *
 * @package Dvsa\Olcs\Transfer\Command\Fieldtype\Trait
 * @author Andy Newton <andy@vitri.ltd>
 */
trait TemplateFolder
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "root",
     *              "gb",
     *              "ni",
     *              "image",
     *              "guides"
     *          }
     *      }
     * )
     */
    protected $templateFolder;

    /**
     * @return string
     */
    public function getTemplateFolder()
    {
        return $this->templateFolder;
    }
}
