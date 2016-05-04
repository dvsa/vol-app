<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("new_application")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NewApplication
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\NewApplicationDetails")
     */
    public $details = null;

    /**
     * @Form\Name("type-of-licence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TypeOfLicence")
     */
    public $typeOfLicence = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Method of application",
     *      "value_options":{
     *          "applied_via_post":"Post",
     *          "applied_via_phone":"Phone"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    protected $appliedVia = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateButtons")
     */
    public $formActions = null;
}
