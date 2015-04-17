<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Fieldset\Address;

/**
 * @Form\Name("opposerAddress")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Options({"label":"Opposer Address"})
 */
class OpposerAddress extends Address
{
}
