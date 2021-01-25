<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Submit")
 */
class SubmitOrCancelApplication
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large govuk-!-margin-right-1",
     *     "id":"submit-button",
     *     "type":"submit"
     * })
     * @Form\Options({"label": "Save and continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("CancelButton")
     * @Form\Attributes({
     *     "id":"cancel-button",
     *     "type":"submit",
     *     "class":"action--secondary large"
     * })
     * @Form\Options({"label": "permits.form.cancel_application.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "id":"save-return-button",
     *     "value":"Save and return to overview",
     *     "role":"link",
     *     "class": "govuk-!-margin-top-7"
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $save = null;
}
