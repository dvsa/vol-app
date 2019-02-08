<?php

namespace Olcs\Form\Model\Form\Licence\Surrender;

use Zend\Form\Annotation as Form;

class Surrender
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Licence\Surrender\Fieldset\Checks")
     */
    protected $checks = null;
}
