<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("editable-content-search")
 */
class EditableTranslationSearch
{
    use IdTrait;

    /**
     * @Form\Type("TextArea")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id": "translatedText",
     *     "class": "extra-long",
     * })
     * @Form\Options({
     *     "label":"Search content",
     *     "hint": "Add content from SelfServe or Internal screens you would like to edit"
     * })
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength", "options":{"max":1024}})
     */
    public $translatedText;
}
