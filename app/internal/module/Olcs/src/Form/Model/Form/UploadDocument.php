<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("upload-document")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class UploadDocument
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("details")
     * @Form\Options({"label":"documents.details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Details")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UploadDocumentFormActions")
     */
    public $formActions = null;
}
