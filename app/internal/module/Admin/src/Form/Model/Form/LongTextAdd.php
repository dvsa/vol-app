<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("LongTextAdd")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LongTextAdd extends Base
{
    /**
     * @Form\Name("longTextDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\LongTextAddDetails")
     */
    public $longTextDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
