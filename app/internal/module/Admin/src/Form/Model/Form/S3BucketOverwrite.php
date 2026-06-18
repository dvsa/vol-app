<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("s3BucketOverwrite")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class S3BucketOverwrite
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\S3BucketOverwrite")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\S3BucketOverwriteActions")
     */
    public $formActions = null;
}
