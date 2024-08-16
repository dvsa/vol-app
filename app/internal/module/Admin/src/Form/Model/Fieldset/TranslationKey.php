<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * ID only fieldset for Javascript population of related Keys
 *
 * @Form\Type("Laminas\Form\Fieldset")
 * @Form\Attributes({"class":"translation-keys"})
 */
class TranslationKey
{
    /**
     * @Form\Attributes({"id":"id"})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Name("translationKey")
     * @Form\Attributes({"id": "translationKey", "data-container-class":"translationKeyContainer js-hidden"})
     * @Form\Options({
     *      "label": "New Translation Key"
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":512})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $translationKey = null;

    /**
     * @Form\Name("description")
     * @Form\Attributes({"id": "translationKey", "data-container-class":"translationKeyContainer js-hidden"})
     * @Form\Options({
     *      "label": "Description"
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":512})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $description = null;
}
