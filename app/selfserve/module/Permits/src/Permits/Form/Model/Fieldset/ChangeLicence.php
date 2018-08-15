<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("CancelApplication")
 */
class ChangeLicence
{

    /**
     * @Form\Name("licenceId")
     * @Form\Required(true)
     * @Form\Type("Hidden")
     */
    public $licenceId = null;


    /**
     * @Form\Name("ConfirmChange")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-change",
     *   "id" : "ConfirmChange",
     * })
     * @Form\Options({
     *   "checked_value": "Yes",
     *   "unchecked_value": "No",
     *   "label": "permits.form.change_licence.label",
     *   "label_attributes": {"class": "form-control form-control--checkbox"},
     *   "must_be_value": "Yes",
     *   "error-message": "permits.form.change_licence.error_message"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $confirmChange = null;
}
