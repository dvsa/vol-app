<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-declarations-main-undertakings")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsMainUndertakings
{
    /**
     * @Form\Attributes({
     *     "value": "markup-psv-main-occupation-heading"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $headingText;

    /**
     * @Form\Attributes({
     *     "value": "markup-psv-main-occupation-records"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $recordsText;

    /**
     * @Form\Options({
     *     "label": "form.checkbox-comply-requirements",
     *     "label_attributes": {"class": "govuk-label govuk-checkboxes__label form-control form-control--checkbox form-control--advanced"},
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "must_be_value": "Y",
     *     "use_hidden_element": false,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $psvOccupationRecordsConfirmation;

    /**
     * @Form\Attributes({
     *     "value": "markup-psv-main-occupation-income"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $incomeText;

    /**
     * @Form\Options({
     *     "label": "form.checkbox-comply-requirements",
     *     "label_attributes": {"class": "govuk-label govuk-checkboxes__label form-control form-control--checkbox form-control--advanced"},
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "must_be_value": "Y",
     *     "use_hidden_element": false,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $psvIncomeRecordsConfirmation;

    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
