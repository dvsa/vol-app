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
     * @Form\Required(false)
     * @Form\ContinueIfEmpty(true)
     * @Form\Attributes({"class":"govuk-checkboxes__item"})
     * @Form\Options({
     *      "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty",
     *      "options": {
     *          "messages": {
     *              "isEmpty": "error.messages.permits.countries"
     *          }
     *      }
     * })
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $countries;
}
