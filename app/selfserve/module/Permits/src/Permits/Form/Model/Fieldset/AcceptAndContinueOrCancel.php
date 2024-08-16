<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("AcceptAndContinueOrCancel")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class AcceptAndContinueOrCancel
{
    /**
     * @Form\Name("AcceptAndContinueButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"permits.button.accept-and-continue",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("CancelButton")
     * @Form\Attributes({
     *     "type":"submit",
     *     "role":"link"
     * })
     * @Form\Options({
     *     "label":"permits.button.cancel-return-to-overview",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;
}
