<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class FormCrudActionsPerson
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "aria-label": "Save and continue"
     * })
     * @Form\Options({
     *     "label": "Save"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({"label": "cancel.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary js-modal-ajax",
     *     "id":"disqualify",
     * })
     * @Form\Options({"label": "Disqualify person"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $disqualify;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({"label": "Save and add another"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $addAnother;
}
