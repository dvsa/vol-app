<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Fieldset\AddressOptional;

/**
 * @Form\Name("opposerAddress")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Options({"label":"Opposer Address"})
 * @Form\Attributes({
 *     "class": "address js-postcode-search"
 * })
 */
class OpposerAddress extends AddressOptional
{
}
