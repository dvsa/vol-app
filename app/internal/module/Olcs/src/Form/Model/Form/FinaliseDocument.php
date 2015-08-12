<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("generate-document")
 * @Form\Attributes({
 *     "method":"post",
 *     "data-close-trigger": "#cancel-finalise",
 *     "class": "js-modal"
 * })
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinaliseDocument
{
    /**
     * @Form\Options({
     *     "label": "documents.data.category"
     * })
     * @Form\Type("\Common\Form\Elements\Types\PlainText")
     */
    public $category = null;

    /**
     * @Form\Options({
     *     "label": "documents.data.sub_category"
     * })
     * @Form\Type("\Common\Form\Elements\Types\PlainText")
     */
    public $subCategory = null;

    /**
     * @Form\Options({
     *     "label": "documents.data.template"
     * })
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $template = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FinaliseDocumentActions")
     */
    public $formActions = null;
}
