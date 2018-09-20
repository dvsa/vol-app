<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class Euro6Emissions
{
    /**
     * @Form\Name("emissions")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--euro6",
     *    "id" : "MeetsEuro6",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "permits.form.euro6.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "1",
     *   "not_checked_message": "error.messages.checkbox.euro6"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $emissions = null;
}
