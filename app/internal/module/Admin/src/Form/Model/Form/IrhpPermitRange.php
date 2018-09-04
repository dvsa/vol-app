<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("IrhpPermitRange")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpPermitRange extends Base
{
    /**
     * @Form\Name("permitRangeDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PermitRangeDetails")
     */
    public $permitRangeDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActionsShort")
     */
    public $formActions = null;
}
