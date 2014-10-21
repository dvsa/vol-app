<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("submission-section-fields")
 * @Form\Options({"label":""})
 */
class SubmissionSection extends Base
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $submission = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $submissionSection = null;

    /**
     * @Form\Attributes({"id":"section_data","class":"extra-long","name":"section_data"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     },
     *     "column-size": "",
     *     "help-block": "Section data"
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $sectionData = null;
}
