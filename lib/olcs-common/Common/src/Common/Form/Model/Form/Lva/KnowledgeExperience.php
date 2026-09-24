<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-knowledge-experience")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class KnowledgeExperience
{
    /**
     * @Form\Attributes({
     *     "value": "markup-knowledge-experience"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -10})
     */
    public $knowledgeExperienceText;

    /**
     * @Form\Name("evidence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FinancialEvidenceEvidence")
     * @Form\Options({
     *     "label": "lva-financial-evidence-evidence.label",
     *     "hint": "knowledge-experience-evidence.hint"
     * })
     * @Form\Flags({"priority": -20})
     */
    public $evidence;

    /**
     * @Form\Attributes({
     *     "value": "markup-knowledge-experience-olat"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -30})
     */
    public $knowledgeExperienceOlatText;

    /**
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     * @Form\Required(false)
     * @Form\Attributes({
     *     "id":"knowledgeExperienceOlat",
     *     "data-container-class":"confirm"
     * })
     * @Form\Options({
     *     "label":"knowledge-experience-olat.label",
     *     "label_attributes":{
     *         "class":"form-control form-control--checkbox form-control--advanced"
     *     },
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "use_hidden_element":false
     * })
     * @Form\Flags({"priority": -40})
     */
    public $knowledgeExperienceOlat;

    /**
     * @Form\Attributes({
     *     "value": "markup-knowledge-experience-courses"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -50})
     */
    public $knowledgeExperienceCourses;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\Flags({"priority": -60})
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
