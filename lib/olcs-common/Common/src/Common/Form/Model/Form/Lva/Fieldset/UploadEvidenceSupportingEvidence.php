<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("SupportingEvidence")
 */
class UploadEvidenceSupportingEvidence
{
    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Attributes({"id":"files"})
     */
    public $files;
}
