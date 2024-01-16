<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 * @Form\Name("partner-name")
 * @Form\Options({"label":"Partner Name"})
 */
class PartnerName extends Base
{
    /**
     * @Form\Attributes({"id":"description","placeholder":"","class":"medium"})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Partner name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":3,"max":35})
     */
    public $description = null;
    /**
     * @Form\Attributes({"value":"ct_partner"})
     * @Form\Type("Hidden")
     */
    public $contactType = 'ct_partner';
}
