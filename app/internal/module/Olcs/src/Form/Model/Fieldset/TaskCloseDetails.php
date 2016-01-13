<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("details")
 */
class TaskCloseDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $label = null;
}
