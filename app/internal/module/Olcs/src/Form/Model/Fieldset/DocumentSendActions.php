<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"govuk-button-group"})
 * @Form\Name("doc-send-actions")
 */
class DocumentSendActions
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "Send by email"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $email = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({
     *     "label": "Print and send by post"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $printAndPost = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "class": "govuk-link",
     *     "id":"close",
     * })
     * @Form\Options({
     *     "label": "Cancel",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $close = null;
}
