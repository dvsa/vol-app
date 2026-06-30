<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterSectionEditContent
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({
     *     "label": "Default Content",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"defaultContent", "class":"extra-long", "name":"defaultContent"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $defaultContent = null;
}
