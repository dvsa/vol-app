<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class FormSaveCancelPreview
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--primary large",
     *     "aria-label": "Save and Continue",
     *     "id": "save"
     * })
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;

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

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--secondary large",
     *     "id": "preview"
     * })
     * @Form\Options({
     *     "label": "Preview",
     *     "keepForReadonly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $preview = null;
}
