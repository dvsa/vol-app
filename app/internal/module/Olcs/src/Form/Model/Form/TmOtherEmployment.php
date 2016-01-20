<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-transport-manager-other-employment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TmOtherEmployment
{
    /**
     * @Form\Name("otherEmployment")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $otherEmployment = null;
}
