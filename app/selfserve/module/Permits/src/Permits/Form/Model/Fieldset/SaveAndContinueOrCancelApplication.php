<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SaveAndContinueOrCancelApplication")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class SaveAndContinueOrCancelApplication
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"Save and continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("CancelButton")
     * @Form\Attributes({
     *     "role":"link",
     * })
     * @Form\Options({
     *     "label":"permits.form.cancel_application.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
