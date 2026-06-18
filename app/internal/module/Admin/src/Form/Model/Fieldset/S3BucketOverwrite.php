<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("fields")
 */
class S3BucketOverwrite
{
    /**
     * @Form\Options({"label":"Replacement file"})
     * @Form\Type("\Laminas\Form\Element\File")
     * @Form\Required(true)
     */
    public $file = null;
}
