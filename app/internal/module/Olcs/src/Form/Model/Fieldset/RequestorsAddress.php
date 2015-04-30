<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Fieldset\Address;

/**
 * @Form\Name("requestorsAddress")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Options({"label":"Requestors Address"})
 * @Form\Attributes({
 *     "class": "address js-postcode-search"
 * })
 */
class RequestorsAddress extends Address
{
}
