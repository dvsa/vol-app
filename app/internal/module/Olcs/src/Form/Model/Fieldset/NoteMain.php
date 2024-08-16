<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("main")
 */
class NoteMain extends Base
{
    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Note"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $comment = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Priority?"})
     * @Form\Type("OlcsCheckbox")
     */
    public $priority;
}
