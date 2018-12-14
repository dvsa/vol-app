<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Sector")
 */
class Sector
{
    /**
     * @Form\Name("sector")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"data-module":"radios"}
     * })
     * @Form\Options({
     *      "error-message":"error.messages.licence",
     *      "label_attributes":{"class": "govuk-label govuk-radios__label govuk-label--s"}
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty"
     * })
     * @Form\Type("DynamicRadio")
     */
    public $sector;
}
