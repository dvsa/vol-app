<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore no methods
 * @Form\Name("task")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Task
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
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $linkType = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $linkId = null;

    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TaskDetails")
     */
    public $details = null;

    /**
     * @Form\Name("assignment")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TaskAssignment")
     */
    public $assignment = null;

    /**
     * @Form\Name("taskHistory")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $taskHistory = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TaskFormActions")
     */
    public $formActions = null;
}
