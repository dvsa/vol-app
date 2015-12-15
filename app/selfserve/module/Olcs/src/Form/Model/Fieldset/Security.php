<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Security")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true, "label": "my-account.form.security.label"})
 */
class Security
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"changePasswordHtml", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $changePasswordHtml;
}
