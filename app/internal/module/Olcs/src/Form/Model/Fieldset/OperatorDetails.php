<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("operator-details")
 */
class OperatorDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Operator name"})
     * @Form\Type("Text")
     */
    public $name = null;
}
