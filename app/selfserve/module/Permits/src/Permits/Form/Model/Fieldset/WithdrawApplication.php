<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class WithdrawApplication
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-withdraw",
     *   "id" : "ConfirmWithdraw",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "permits.form.withdraw_application.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox"},
     *   "must_be_value": "1",
     *   "not_checked_message": "permits.form.withdraw_application.error_message"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $confirmWithdraw = null;
}
