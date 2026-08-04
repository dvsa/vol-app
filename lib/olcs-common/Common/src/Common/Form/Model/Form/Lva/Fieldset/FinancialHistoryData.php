<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class FinancialHistoryData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"value":"markup-application_previous-history_financial-history-finance-hint"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $financeHint;

    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Attributes({"value":"<h3 id=""mainq"">%s</h3>"})
     * @Form\Options({"tokens": {"application_previous-history_financial-history.intro"}})
     */
    public $hasAnyPerson;

    /**
     * @Form\Options({
     *     "short-label": "short-label-financial-history-bankrupt",
     *     "label": "application_previous-history_financial-history.finance.bankrupt",
     *     "legend-attributes": {"class": "form-element__label", "aria-describedby": "mainq"},
     *     "error-message": "financialHistoryData_bankrupt-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"bankrupt"}
     * })
     * @Form\Type("radio")
     */
    public $bankrupt;

    /**
     * @Form\Options({
     *     "short-label": "short-label-financial-history-liquidation",
     *     "label": "application_previous-history_financial-history.finance.liquidation",
     *     "legend-attributes": {"class": "form-element__label", "aria-describedby": "mainq"},
     *     "error-message": "financialHistoryData_liquidation-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"liquidation"}
     * })
     * @Form\Type("radio")
     */
    public $liquidation;

    /**
     * @Form\Options({
     *     "short-label": "short-label-financial-history-receivership",
     *     "label": "application_previous-history_financial-history.finance.receivership",
     *     "legend-attributes": {"class": "form-element__label", "aria-describedby": "mainq"},
     *     "error-message": "financialHistoryData_receivership-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id": "receiversip"}
     * })
     * @Form\Type("radio")
     */
    public $receivership;

    /**
     * @Form\Options({
     *     "short-label": "short-label-financial-history-administration",
     *     "label": "application_previous-history_financial-history.finance.administration",
     *     "legend-attributes": {"class": "form-element__label", "aria-describedby": "mainq"},
     *     "error-message": "financialHistoryData_administration-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"administration"}
     * })
     * @Form\Type("radio")
     */
    public $administration;

    /**
     * @Form\Options({
     *     "short-label": "short-label-financial-history-disqualified",
     *     "fieldset-attributes": {"id":"disqualified"},
     *     "legend-attributes": {"class": "form-element__label", "aria-describedby": "mainq"},
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label": "application_previous-history_financial-history.finance.disqualified",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "error-message": "financialHistoryData_disqualified-error"
     * })
     * @Form\Type("radio")
     */
    public $disqualified;

    /**
     * @Form\Attributes({
     *     "value":"markup-application_previous-history_financial-history-insolvencyDetails-hint"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $additionalInfoLabel;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "required": false,
     *     "id": "",
     *     "class": "long js-financial-history",
     *     "placeholder": "application_previous-history_financial-history.insolvencyDetails.placeholder",
     *     "x-js-hint-chars-count": "application_previous-history_financial-history.insolvencyDetails.count-hint",
     * })
     * @Form\Options({
     *     "short-label": "short-label-financial-history-additional-information",
     *     "label": "short-label-financial-history-additional-information",
     *     "error-message": "financialHistoryData_insolvencyDetails-error",
     *     "label_attributes": {
     *         "class": "govuk-visually-hidden",
     *         "id": "additional-information"
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo")
     */
    public $insolvencyDetails;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Attributes({"id":"file"})
     */
    public $file;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Lva\Fieldset\FinancialHistoryConfirmation")
     */
    public $financialHistoryConfirmation;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $niFlag;
}
