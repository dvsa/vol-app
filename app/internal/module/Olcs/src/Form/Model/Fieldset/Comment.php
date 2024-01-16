<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("comment-fields")
 * @Form\Options({"label":""})
 */
class Comment extends Base
{
    /**
     * @Form\Attributes({"id":"comment","class":"extra-long","name":"comment"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $comment = null;
}
