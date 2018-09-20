<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class DeclineApplicationFieldset
{
    /**
     * @Form\Name("DeclineApplicationFieldset")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-decline",
     *   "id" : "DeclineApplication",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "permits.form.declined_permit.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox"},
     *   "must_be_value": "1",
     *   "not_checked_message": "permits.form.declined_permit.error_message"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $confirmDecline = null;
}
