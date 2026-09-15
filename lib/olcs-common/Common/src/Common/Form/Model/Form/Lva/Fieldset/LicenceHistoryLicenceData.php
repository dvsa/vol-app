<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class LicenceHistoryLicenceData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $previousLicenceType;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label":"selfserve-app-subSection-previous-history-licence-history-licNo",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "licenceHistoryLicenceData_licNo-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $licNo;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-licence-history-holderName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "licenceHistoryLicenceData_holderName-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $holderName;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-licence-history-willSurrender",
     *     "error-message": "licenceHistoryLicenceData_willSurrender-error",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $willSurrender;

    /**
     * @Form\Attributes({
     *     "id":"willSurrenderMessage",
     *     "data-container-class": "will-surrender",
     *     "value": "application_add-licence-history-will-surrender-hint",
     *     "class": "will-surrender-message"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $willSurrenderMessage;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob","required":false})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-licence-history-disqualificationDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $disqualificationDate;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-licence-history-disqualificationLength",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $disqualificationLength;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob","required":false})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-licence-history-purchaseDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $purchaseDate;
}
