<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("operator-id")
 */
class OperatorId
{
    /**
     * @Form\Options({"label": "internal-operator-id"})
     * @Form\Name("operator-id")
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     */
    public $operatorId = null;
}
