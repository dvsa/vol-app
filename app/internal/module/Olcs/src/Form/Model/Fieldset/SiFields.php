<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class SiFields extends CaseBase
{
    /**
     * @Form\Attributes({"id":"notificationNumber"})
     * @Form\Options({"label": "Notification Number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":36})
     */
    public $notificationNumber = null;

    /**
     * @Form\Attributes({"id":"siCategoryType","class":"chosen-select-medium"})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\SiCategoryType"
     * })
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     */
    public $siCategoryType;

    /**
     * @Form\Attributes({"id":"infringementDate"})
     * @Form\Options({
     *     "label": "Date of infringement"
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $infringementDate;

    /**
     * @Form\Attributes({"id":"checkDate"})
     * @Form\Options({
     *     "label": "Date of check"
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $checkDate;

    /**
     * @Form\Attributes({"id":"memberStateCode","class":"chosen-select-medium"})
     * @Form\Options({
     *     "label": "Member state",
     *     "empty_option": "",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "category": "isMemberState",
     *     "use_groups": false
     * })
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     */
    public $memberStateCode;

    /**
     * @Form\Attributes({"id":"reason","class":"extra-long"})
     * @Form\Options({"label":"Reason"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":5000})
     */
    public $reason = null;
}
