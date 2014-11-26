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
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"internal-operator-profile-name"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Name("name")
     * @Form\Type("Text")
     */
    public $name = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium",  "multiple" : true})
     * @Form\Options({
     *     "label": "Nature of Business",
     *     "help-block": "Please select a nature of business",
     *     "category":"SIC_CODE"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $natureOfBusiness = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({
     *      "value":
     *      "Please enter your business type. You can find a list of business types at Companies House 
     *      <a href=""http://www.companieshouse.gov.uk/infoAndGuide/faq/sicCode.shtml"" target=""_blank"">here</a>"})
     */
    public $information = null;

    /**
     * @Form\Attributes({"chosen-size":"long","id":""})
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
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Type("Hidden")
     */
    public $personVersion = null;
}
