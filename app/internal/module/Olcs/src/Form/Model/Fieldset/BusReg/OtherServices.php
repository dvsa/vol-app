<?php

namespace Olcs\Form\Model\Fieldset\BusReg;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-other-services")
 */
class OtherServices extends Base
{
    /**
     * @Form\Attributes({"class":"","id":"serviceNo"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":70}})
     */
    public $serviceNo = null;
}
