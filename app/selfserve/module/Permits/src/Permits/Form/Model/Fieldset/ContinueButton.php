<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("ContinueButton")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class ContinueButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "id":"submit-continue-button",
     * })
     * @Form\Options({
     *     "label":"permits.button.continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
