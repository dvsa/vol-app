<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitConfirm")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class SubmitConfirm
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "role":"button"
     * })
     * @Form\Options({
     *     "label":"permits.button.confirm-and-continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "id":"save-return-button",
     *     "type":"submit",
     *     "class":"govuk-button govuk-button--secondary",
     *     "data-module": "govuk-button",
     * })
     * @Form\Options({
     *     "label":"permits.button.save-return-to-overview",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;
}
