<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Name("upload-evidence")
 * @Form\Options({"prefer_form_input_filter":true})
 */
class UploadEvidence
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type(Hidden::class)
     */
    public ?Hidden $correlationId = null;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\UploadEvidenceFinancialEvidence")
     * @Form\Options({
     *    "label": "lva.section.title.upload-evidence.financial-evidence"
     * })
     * @Form\Name("financialEvidence")
     */
    public $financialEvidence;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\UploadEvidenceOperatingCentre",
     *      true,
     *      options={
     *          "count": 1,
     *          "label":"lva.section.title.upload-evidence.operating-centres",
     *      }
     * )
     * @Form\Name("operatingCentres")
     */
    public $operatingCentres;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\UploadEvidenceSupportingEvidence",
     *     false,
     *      options={
     *          "count": 1,
     *          "label":"lva.section.title.upload-evidence.supporting-evidence",
     *      }
     * )
     * @Form\Name("supportingEvidence")
     */
    public $supportingEvidence;


    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "Save and continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     * @Form\Flags({"priority": -10})
     */
    public $saveAndContinue;
}
