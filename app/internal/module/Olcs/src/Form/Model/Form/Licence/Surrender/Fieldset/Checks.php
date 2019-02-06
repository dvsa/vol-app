<?php

namespace Olcs\Form\Model\Form\Licence\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

class Checks
{
    /**
     * @Form\Type("\Zend\Form\Element\Checkbox ")
     * @Form\Options({
     *     "label": "Digital signature has been checked"
     * })
     */
    public $digitalSignature = null;

    /**
     * @Form\Type("\Zend\Form\Element\Checkbox ")
     * @Form\Options({
     *     "label": "ECMS has been checked"
     * })
     */
    public $ecms = null;
}
