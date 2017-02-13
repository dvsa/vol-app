<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("operator-details")
 */
class OperatorDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Options({"label":"application_your-business_business-details.data.company_number"})
     * @Form\Type("Common\Form\Elements\Types\CompanyNumber")
     */
    public $companyNumber = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"internal-operator-profile-name"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Name("name")
     * @Form\Type("Text")
     */
    public $name = null;

    /**
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({
     *     "label": "Nature of Business"
     * })
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $natureOfBusiness = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({
     *      "value":
     *      "Please enter your business type. You can find a list of business types at Companies House
     *      <a href=""https://www.gov.uk/government/publications/standard-industrial-classification-of-economic-activities-sic"" target=""_blank"">here</a>"})
     */
    public $information = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"internal-operator-profile-first-name"})
     * @Form\Type("Text")
     */
    public $firstName = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"internal-operator-profile-last-name"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Type("Text")
     */
    public $lastName = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Is IRFO"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isIrfo;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "How would you like to receive your correspondence?",
     *      "value_options":{
     *          "N":"Post",
     *          "Y":"Email"
     *      },
     * }),
     * @Form\Attributes({
     *      "value":"N"
     * })
     */
    public $allowEmail;

    /**
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Type("Hidden")
     */
    public $personVersion = null;
}
