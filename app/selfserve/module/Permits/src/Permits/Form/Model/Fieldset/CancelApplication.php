<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class CancelApplication
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-cancel",
     *   "id" : "ConfirmCancel",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "permits.form.cancel_application.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox"},
     *   "must_be_value": "1",
     *   "not_checked_message": "permits.form.cancel_application.error_message"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $confirmCancel = null;
}
