<?php

namespace Olcs\Form\Model\Form\Licence\Surrender;

use Zend\Form\Annotation as Form;

/**
 * Class Surrender
 * @package Olcs\Form\Model\Form\Licence\Surrender
 */
class Surrender
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Licence\Surrender\Fieldset\Checks")
     * @Form\Attributes({
     *     "class":"surrenderChecks",
     * })
     */
    protected $checks = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Licence\Surrender\Fieldset\Actions")
     */
    protected $actions = null;
}
