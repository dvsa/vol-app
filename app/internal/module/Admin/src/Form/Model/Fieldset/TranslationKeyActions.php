<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class TranslationKeyActions
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--primary large",
     *     "aria-label": "Save and replace"
     * })
     * @Form\Options({"label": "Save and replace"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--secondary large",
     *     "id": "cancel"
     * })
     * @Form\Options({
     *     "label": "Cancel",
     *     "keepForReadonly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
