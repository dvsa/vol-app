<?php

namespace Admin\Form\Model\Form;

use Olcs\Form\Model\Fieldset\Base;
use Laminas\Form\Annotation as Form;
use Laminas\Form\FormInterface;

/**
 * @Form\Name("public-holiday")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class PublicHoliday extends Base
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PublicHoliday")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
