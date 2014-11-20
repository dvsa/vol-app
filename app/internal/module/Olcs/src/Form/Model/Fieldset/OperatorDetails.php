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
     * @Form\Options({"label":"Operator name"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Name("name")
     * @Form\Type("Text")
     */
    public $name = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"First name"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Type("Text")
     */
    public $firstName = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Last name"})
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
