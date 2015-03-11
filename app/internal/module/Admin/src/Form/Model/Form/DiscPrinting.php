<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("admin_disc-printing")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class DiscPrinting
{
    /**
     * @Form\Name("type-of-licence")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\TypeOfLicence")
     */
    public $typeOfLicence = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateButtons")
     */
    public $formActions = null;
}
