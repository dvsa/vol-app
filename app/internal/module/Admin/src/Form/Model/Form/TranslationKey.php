<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("translationKey")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"label": "Edit translation key"})
 */
class TranslationKey
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
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\TranslationKeyActions")
     */
    public $formActions = null;
}
