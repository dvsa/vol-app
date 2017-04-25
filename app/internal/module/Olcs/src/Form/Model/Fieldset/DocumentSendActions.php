<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("doc-send-actions")
 */
class DocumentSendActions
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--primary",
     * })
     * @Form\Options({
     *     "label": "Email"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $email = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--primary",
     * })
     * @Form\Options({
     *     "label": "Print and post"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $printAndPost = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"cancel"})
     * @Form\Options({
     *     "label": "Cancel",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
