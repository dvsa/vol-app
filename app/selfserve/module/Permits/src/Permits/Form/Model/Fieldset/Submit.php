<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Submit")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class Submit
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"submitbutton",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"Save and continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "class":"govuk-button govuk-button--secondary",
     *     "data-module": "govuk-button",
     *     "id":"save-return-button",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"Save and return to overview",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;
}
