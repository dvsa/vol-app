<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("lastModifiedBy")
 * @Form\Options({
 *     "label": "tasks.lastModifiedBy",
 * })
 */
class TaskLastModifiedBy
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $lastModifiedByDetails = null;
}
