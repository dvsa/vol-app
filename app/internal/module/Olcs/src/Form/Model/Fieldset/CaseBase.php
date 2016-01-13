<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("case-base")
 * @Form\Attributes({"class": "visually-hidden"})
 */
class CaseBase extends Base
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;
}
