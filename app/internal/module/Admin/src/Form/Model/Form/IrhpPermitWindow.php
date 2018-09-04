<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("IrhpPermitWindow")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpPermitWindow extends Base
{
    /**
     * @Form\Name("permitWindowDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PermitWindowDetails")
     */
    public $permitWindowDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActionsShort")
     */
    public $formActions = null;
}
