<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("Partial")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Partial extends Base
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\TranslationKey")
     */
    public $fields = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $jsonUrl = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $resultsKey = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $translationVar = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\TranslationKeyActions")
     */
    public $formActions = null;
}
