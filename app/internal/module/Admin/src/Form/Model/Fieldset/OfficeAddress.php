<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Fieldset\Address;

/**
 * @Form\Name("officeAddress")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Options({"label":"Office Address"})
 */
class OfficeAddress extends Address
{
}
