<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("CancelButton")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class CancelButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"cancelbutton",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"permits.form.cancel_application.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
