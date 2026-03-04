<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("clear-cache")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ClearCache
{
    /**
     * @Form\Name("clear-cache")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ClearCache")
     */
    public $clearCache = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ClearCacheActions")
     */
    public $formActions = null;
}
