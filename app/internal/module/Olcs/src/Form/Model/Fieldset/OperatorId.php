<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"govuk-button-group"})
 * @Form\Name("operator-id")
 */
class OperatorId
{
    /**
     * @Form\Options({"label": "internal-operator-id"})
     * @Form\Name("operator-id")
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     */
    public $operatorId = null;
}
