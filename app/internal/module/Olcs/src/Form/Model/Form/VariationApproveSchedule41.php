<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("variation-approve-schedule41")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class VariationApproveSchedule41
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Is this a true schedule 4/1?",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      }
     * })
     */
    protected $isTrueS4 = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ConfirmButtons")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
