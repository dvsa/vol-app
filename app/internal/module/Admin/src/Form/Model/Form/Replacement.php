<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("Replacement")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Replacement extends Base
{
    /**
     * @Form\Name("replacementDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ReplacementDetails")
     */
    public $replacementDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
