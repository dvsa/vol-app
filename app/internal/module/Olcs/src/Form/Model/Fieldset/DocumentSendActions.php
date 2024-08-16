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
     *     "label": "Email"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $email = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "Print and post"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $printAndPost = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"close",
     * })
     * @Form\Options({
     *     "label": "Close",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $close = null;
}
