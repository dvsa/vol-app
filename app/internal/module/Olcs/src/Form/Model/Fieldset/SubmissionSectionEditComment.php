<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("submission-section-comment-fields")
 * @Form\Options({"label":""})
 */
class SubmissionSectionEditComment extends Base
{
    /**
     * @Form\Attributes({"id":"","class":"extra-long tinymce","name":"comment"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Filter({"name":"htmlpurifier"})
     */
    public $comment = null;
}
