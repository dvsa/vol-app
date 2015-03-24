<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-contact")
 * @Form\Options({"label":"Contact"})
 */
class UserContactDetails
{
    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"phone","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Phone"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     */
    public $phone = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"fax","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Fax"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     */
    public $fax = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":"Address"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $address = null;
}
