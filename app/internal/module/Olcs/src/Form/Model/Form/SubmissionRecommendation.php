<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("submission-recommendation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label":"Submission Recommendation"})
 */
class SubmissionRecommendation
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\SubmissionRecommendation")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
