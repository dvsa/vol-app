<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("class Countries")
 */
class Countries
{
    /**
     * @Form\Name("countries")
     * @Form\Required(true)
     * @Form\Attributes({"class":"govuk-checkboxes__item"})
     * @Form\Options({
     *      "error-message":"error.messages.permits.countries",
     *      "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty"
     * })
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $countries;
}
