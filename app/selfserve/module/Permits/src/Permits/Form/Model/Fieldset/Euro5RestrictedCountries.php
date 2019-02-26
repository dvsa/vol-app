<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class Euro5RestrictedCountries
{
    /**
     * @Form\Name("restrictedCountries")
     * @Form\Attributes({
     *   "class" : "input--euro5-countries",
     *    "id" : "Euro5Countries",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "permits.form.restricted.countries.euro5.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "1",
     *   "not_checked_message": "error.messages.restricted.countries.euro5"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $restrictedCountries = null;
}
