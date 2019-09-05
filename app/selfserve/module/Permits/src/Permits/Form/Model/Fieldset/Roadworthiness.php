<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class Roadworthiness
{
    /**
     * @Form\Name("roadworthiness")
     * @Form\Attributes({
     *   "class" : "input--roadworthiness",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.roadworthiness.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1",
     *     "not_checked_message": "error.messages.checkbox.roadworthiness"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $roadworthiness = null;
}
