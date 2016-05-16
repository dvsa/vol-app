<?php
/**
 * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
 */
namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("create-task-temp")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class CreateTaskTemp
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\CreateTaskTempDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateButtons")
     */
    public $formActions = null;
}
