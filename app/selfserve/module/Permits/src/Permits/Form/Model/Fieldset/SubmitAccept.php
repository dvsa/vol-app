<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitAccept")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class SubmitAccept
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"submit-accept-button",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"permits.button.accept-and-continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "id":"save-return-button",
     *     "type":"submit",
     *     "role":"link"
     * })
     * @Form\Options({
     *     "label":"Save and return to overview",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;
}
