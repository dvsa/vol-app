<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Business details fieldset
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetails
{
    /**
     * @Form\Options({"label":"application_your-business_business-details.data.company_number"})
     * @Form\Attributes({"id": "companyNumber"})
     * @Form\Type("Common\Form\Elements\Types\CompanyNumber")
     */
    public $companyNumber;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"application_your-business_business-details.data.company_name",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={
*     "min": 0, "max": 200
     *})
     */
    public $name;

    /**
     * @Form\Attributes({"class":"add-another"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TradingNames",
     *      true,
     *      options={
     *          "count": 1,
     *          "label":"application_your-business_business-details.data.trading_names_optional",
     *          "hint":"markup-trading-name-hint",
     *          "hint-position": "below",
     *      }
     * )
     */
    public $tradingNames;

    /**
     * @Form\Attributes({"id":"natureOfBusiness","placeholder":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Nature of business",
     *     "error-message" : "businessDetails_natureOfBusiness-error",
     *     "label_attributes": {
     *         "aria-label": "businessDetails_natureOfBusiness-error",
     *         "class": "form-element__question"
     *     }
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={
     *     "min": 0, "max": 200
     *})
     */
    public $natureOfBusiness;
}
