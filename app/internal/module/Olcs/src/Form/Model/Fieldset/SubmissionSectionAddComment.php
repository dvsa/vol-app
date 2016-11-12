<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("submission-section-comment-fields")
 * @Form\Options({"label":""})
 */
class SubmissionSectionAddComment extends Base
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
     * @Form\Attributes({"id":"","class":"extra-long tinymce","name":"comment"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     },
     *     "column-size": "",
     *     "help-block": "Comment"
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"htmlpurifier"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5}})
     */
    public $comment = null;
}
