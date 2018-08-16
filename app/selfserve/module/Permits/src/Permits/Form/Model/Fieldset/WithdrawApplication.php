<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("WithdrawApplication")
 */
class WithdrawApplication
{
    /**
     * @Form\Name("ConfirmWithdraw")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-withdraw",
     *   "id" : "ConfirmWithdraw",
     * })
     * @Form\Options({
     *   "checked_value": "Yes",
     *   "unchecked_value": "No",
     *   "label": "permits.form.withdraw_application.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox"},
     *   "must_be_value": "Yes",
     *   "error-message": "permits.form.withdraw_application.error_message"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $confirmWithdraw = null;
}
