<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("MyDetails")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class MyDetails
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\MyDetails")
     */
    public $fields = null;

    /**
     * @Form\Name("securityFields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Security")
     */
    public $securityFields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
