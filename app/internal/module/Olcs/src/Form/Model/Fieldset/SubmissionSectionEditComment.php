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
     * @Form\Attributes({"id":"","class":"extra-long","name":"comment"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("EditorJs")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $comment = null;
}
