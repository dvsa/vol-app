<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-declarations-evidence-large")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsEvidenceLarge
{
    /**
     * @Form\Attributes({
     *     "value": "markup-psv-large-evidence-form"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $largeEvidenceText;

    /**
     * @Form\Name("evidence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FinancialEvidenceEvidence")
     * @Form\Options({
     *    "label": "lva-financial-evidence-evidence.label",
     *    "hint": "lva-psv-evidence.hint"
     * })
     */
    public $evidence;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;
}
