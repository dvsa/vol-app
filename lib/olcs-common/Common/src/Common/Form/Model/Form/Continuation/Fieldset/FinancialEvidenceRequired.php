<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Options({
 *     "label" : "continuations.financial-evidence-required.label",
 *     "hint" : "markup-continuation-financial-evidence-required-hint",
 * })
 */
class FinancialEvidenceRequired
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Radio")
     * @Form\Options({
     *     "value_options": {
     *          "upload": "lva-continuations-financial-evidence-upload-now.yes",
     *      },
     * })
     * @Form\ErrorMessage("continuations.financial-evidence-required.error")
     */
    public $radio;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     */
    public $uploadContent;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"Continue"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $submit;
}
