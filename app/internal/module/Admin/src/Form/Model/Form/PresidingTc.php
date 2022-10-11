<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("task-allocation-rule")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class PresidingTc extends Base
{
    /**
     * @Form\Name("presidingTcDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PresidingTcDetails")
     */
    public $presidingTcDtails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
