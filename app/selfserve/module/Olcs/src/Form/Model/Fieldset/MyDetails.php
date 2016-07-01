<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("MyDetails")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true, "label": "my-account.form.my-details.label"})
 */
class MyDetails extends Base
{
    /**
     * @Form\Options({"label":"user-name"})
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     */
    public $loginId = null;

    /**
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"first-name"})
     * @Form\Type("Text")
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"last-name"})
     * @Form\Type("Text")
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"email-address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\EmailConfirm","options":{"token":"emailConfirm"}})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"confirm-email-address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $emailConfirm = null;

    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":""})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;
}
