<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore file with no methods
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
