<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SaveAndReturnOnly")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class SaveAndReturnOnly
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"saveandreturnbutton",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"save.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
