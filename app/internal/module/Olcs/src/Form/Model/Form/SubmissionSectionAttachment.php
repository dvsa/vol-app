<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class SubmissionSectionAttachment
{
    /**
     * @Form\Name("sectionId")
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $sectionId = null;

    /**
     * @Form\Name("attachments")
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Options({
     * })
     */
    public $attachments = null;
}
